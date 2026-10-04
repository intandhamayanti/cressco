<?php

namespace App\Services;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class PaymentReminderService
{
    /**
     * Check if payment is eligible for a follow-up reminder.
     */
    public function isEligible(Payment $payment): bool
    {
        return in_array($payment->status, ['belum_bayar', 'menunggu_verifikasi', 'terlambat'], true);
    }

    /**
     * Generate reminder data and formatted message text.
     *
     * @return array{
     *     eligible: bool,
     *     type: string,
     *     student_name: string,
     *     parent_name: string,
     *     parent_phone: ?string,
     *     wa_phone: ?string,
     *     period: string,
     *     amount: float,
     *     formatted_amount: string,
     *     due_date: ?string,
     *     formatted_due_date: string,
     *     status: string,
     *     status_label: string,
     *     message: string,
     *     wa_link: ?string
     * }
     */
    public function generate(Payment $payment, ?string $customNote = null): array
    {
        $payment->loadMissing(['student', 'branch', 'tenant']);

        $student = $payment->student;
        $branch = $payment->branch;
        $tenant = $payment->tenant;

        $studentName = $student?->name ?? 'Siswa';
        $parentName = $student?->parent_name ?: 'Orang Tua Siswa';
        $parentPhone = $student?->parent_phone ?: $student?->phone;
        $tenantName = $tenant?->name ?? 'Cressco Bimbel';
        $branchName = $branch?->name ?? 'Cabang';

        $amount = (float) $payment->amount;
        $formattedAmount = 'Rp '.number_format($amount, 0, ',', '.');
        $dueDate = $payment->due_date;
        $formattedDueDate = $dueDate ? Carbon::parse($dueDate)->format('d/m/Y') : '-';

        $isOverdue = $payment->status === 'terlambat' || ($payment->status === 'belum_bayar' && $dueDate && Carbon::parse($dueDate)->isPast());
        $isPending = $payment->status === 'menunggu_verifikasi';
        $isPaid = $payment->status === 'lunas';

        $eligible = $this->isEligible($payment);

        if ($isPaid) {
            $type = 'lunas';
            $statusLabel = 'LUNAS';
            $message = "Halo Bapak/Ibu {$parentName},\n\nTerima kasih, pembayaran bimbingan belajar di {$tenantName} ({$branchName}) untuk ananda {$studentName}:\n- Periode: {$payment->period}\n- Nominal: {$formattedAmount}\n- Status: LUNAS\n\nTelah kami terima dan diverifikasi. Terima kasih atas kerja samanya.";
        } elseif ($isOverdue) {
            $type = 'overdue';
            $statusLabel = 'TERLAMBAT (JATUH TEMPO)';
            $message = "Halo Bapak/Ibu {$parentName},\n\nKami dari {$tenantName} ({$branchName}) menginformasikan bahwa tagihan bimbingan belajar untuk ananda {$studentName}:\n- Periode: {$payment->period}\n- Nominal: {$formattedAmount}\n- Batas Waktu (Jatuh Tempo): {$formattedDueDate}\n- Status: TERLAMBAT\n\nTagihan ini telah melewati batas waktu jatuh tempo. Mohon untuk segera menyelesaikan pembayaran melalui transfer atau langsung ke cabang. Jika sudah melakukan transfer, mohon konfirmasi dengan mengirimkan bukti pembayaran.\n\nTerima kasih atas perhatian dan kerja samanya.";
        } elseif ($isPending) {
            $type = 'pending_verification';
            $statusLabel = 'MENUNGGU VERIFIKASI';
            $message = "Halo Bapak/Ibu {$parentName},\n\nKami dari {$tenantName} ({$branchName}) telah mencatat pengajuan pembayaran bimbingan belajar untuk ananda {$studentName}:\n- Periode: {$payment->period}\n- Nominal: {$formattedAmount}\n- Status: MENUNGGU VERIFIKASI\n\nPembayaran Anda sedang dalam proses verifikasi oleh tim kasir/admin kami. Kami akan menginformasikan kembali setelah diverifikasi.\n\nTerima kasih.";
        } else {
            $type = 'unpaid';
            $statusLabel = 'BELUM BAYAR';
            $message = "Halo Bapak/Ibu {$parentName},\n\nKami dari {$tenantName} ({$branchName}) menginformasikan tagihan bimbingan belajar untuk ananda {$studentName}:\n- Periode: {$payment->period}\n- Nominal: {$formattedAmount}\n- Batas Waktu: {$formattedDueDate}\n- Status: Belum Bayar\n\nPembayaran dapat ditransfer atau diserahkan langsung ke petugas cabang. Jika sudah melakukan pembayaran, mohon abaikan pesan ini.\n\nTerima kasih atas perhatian dan kerja samanya.";
        }

        if ($customNote) {
            $message .= "\n\nCatatan Tambahan: {$customNote}";
        }

        // WhatsApp Phone & Link
        $waPhone = null;
        $waLink = null;
        if ($parentPhone) {
            $cleaned = preg_replace('/[^0-9]/', '', $parentPhone);
            if (str_starts_with($cleaned, '0')) {
                $cleaned = '62'.substr($cleaned, 1);
            }
            if (strlen($cleaned) >= 9) {
                $waPhone = $cleaned;
                $waLink = 'https://wa.me/'.$waPhone.'?text='.urlencode($message);
            }
        }

        return [
            'eligible' => $eligible,
            'type' => $type,
            'student_name' => $studentName,
            'parent_name' => $parentName,
            'parent_phone' => $parentPhone,
            'wa_phone' => $waPhone,
            'period' => $payment->period,
            'amount' => $amount,
            'formatted_amount' => $formattedAmount,
            'due_date' => $dueDate ? Carbon::parse($dueDate)->toDateString() : null,
            'formatted_due_date' => $formattedDueDate,
            'status' => $payment->status,
            'status_label' => $statusLabel,
            'message' => $message,
            'wa_link' => $waLink,
        ];
    }

    /**
     * Alias for generate
     */
    public function generateReminder(Payment $payment, ?string $customNote = null): array
    {
        $res = $this->generate($payment, $customNote);
        $res['text'] = $res['message'];

        return $res;
    }

    /**
     * Get list of payments requiring reminder follow-up.
     */
    public function getFollowUpPayments(string $tenantId, ?array $accessibleBranchIds = null): Collection
    {
        $query = Payment::where('tenant_id', $tenantId)
            ->whereIn('status', ['belum_bayar', 'menunggu_verifikasi', 'terlambat'])
            ->with(['student', 'branch', 'enrollment.classModel']);

        if ($accessibleBranchIds !== null) {
            $query->whereIn('branch_id', $accessibleBranchIds);
        }

        return $query->orderBy('due_date', 'asc')->get();
    }
}
