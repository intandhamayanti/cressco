<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Allowed status transitions
     */
    protected const ALLOWED_TRANSITIONS = [
        'belum_bayar' => ['menunggu_verifikasi', 'lunas', 'terlambat'],
        'terlambat' => ['menunggu_verifikasi', 'lunas'],
        'menunggu_verifikasi' => ['lunas', 'belum_bayar', 'terlambat'],
        'lunas' => ['lunas'], // Final state
    ];

    /**
     * Record a new payment invoice.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function recordPayment(array $data, User $actor, ?UploadedFile $proofFile = null): Payment
    {
        $tenantId = $data['tenant_id'] ?? ($actor->tenant_id ?? TenantContext::getTenantId());
        $studentId = $data['student_id'];
        $period = $data['period'];
        $enrollmentId = $data['enrollment_id'] ?? null;

        // Prevent duplicate payment records for same tenant, student, period, and enrollment
        $duplicateQuery = Payment::where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->where('period', $period);

        if ($enrollmentId) {
            $duplicateQuery->where('enrollment_id', $enrollmentId);
        } else {
            $duplicateQuery->whereNull('enrollment_id');
        }

        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'period' => 'Tagihan untuk siswa dan periode ini sudah terdaftar.',
            ]);
        }

        // Handle proof file if provided
        $notes = $data['notes'] ?? '';
        if ($proofFile) {
            $path = $proofFile->store('payment-proofs', 'public');
            $proofNote = "[Bukti Pembayaran: /storage/{$path}]";
            $notes = trim($notes ? "{$notes}\n{$proofNote}" : $proofNote);

            // Auto-transition to menunggu_verifikasi if created with belum_bayar
            if (($data['status'] ?? 'belum_bayar') === 'belum_bayar') {
                $data['status'] = 'menunggu_verifikasi';
            }
        }

        $status = $data['status'] ?? 'belum_bayar';
        $paidAt = $data['paid_at'] ?? null;

        if ($status === 'lunas' && empty($paidAt)) {
            $paidAt = now();
        }

        return Payment::create([
            'tenant_id' => $tenantId,
            'branch_id' => $data['branch_id'],
            'student_id' => $studentId,
            'enrollment_id' => $enrollmentId,
            'period' => $period,
            'amount' => $data['amount'],
            'due_date' => $data['due_date'],
            'paid_at' => $paidAt,
            'status' => $status,
            'notes' => $notes ?: null,
            'recorded_by' => $actor->id,
        ]);
    }

    /**
     * Update an existing payment.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function updatePayment(Payment $payment, array $data, User $actor, ?UploadedFile $proofFile = null): Payment
    {
        $tenantId = $payment->tenant_id;
        $studentId = $data['student_id'] ?? $payment->student_id;
        $period = $data['period'] ?? $payment->period;
        $enrollmentId = array_key_exists('enrollment_id', $data) ? $data['enrollment_id'] : $payment->enrollment_id;

        // Prevent duplicate when changing period or student/enrollment
        $duplicateQuery = Payment::where('tenant_id', $tenantId)
            ->where('student_id', $studentId)
            ->where('period', $period)
            ->where('id', '!=', $payment->id);

        if ($enrollmentId) {
            $duplicateQuery->where('enrollment_id', $enrollmentId);
        } else {
            $duplicateQuery->whereNull('enrollment_id');
        }

        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'period' => 'Tagihan untuk siswa dan periode ini sudah terdaftar.',
            ]);
        }

        // Validate status transition
        if (isset($data['status']) && $data['status'] !== $payment->status) {
            $this->validateStatusTransition($payment->status, $data['status']);
        }

        $notes = $data['notes'] ?? $payment->notes;
        if ($proofFile) {
            $path = $proofFile->store('payment-proofs', 'public');
            $proofNote = "[Bukti Pembayaran: /storage/{$path}]";
            $notes = trim($notes ? "{$notes}\n{$proofNote}" : $proofNote);

            if (($data['status'] ?? $payment->status) === 'belum_bayar' || ($data['status'] ?? $payment->status) === 'terlambat') {
                $data['status'] = 'menunggu_verifikasi';
            }
        }

        $status = $data['status'] ?? $payment->status;
        $paidAt = $data['paid_at'] ?? $payment->paid_at;

        if ($status === 'lunas' && empty($paidAt)) {
            $paidAt = now();
        }

        $payment->update([
            'branch_id' => $data['branch_id'] ?? $payment->branch_id,
            'student_id' => $studentId,
            'enrollment_id' => $enrollmentId,
            'period' => $period,
            'amount' => $data['amount'] ?? $payment->amount,
            'due_date' => $data['due_date'] ?? $payment->due_date,
            'paid_at' => $paidAt,
            'status' => $status,
            'notes' => $notes ?: null,
        ]);

        return $payment->fresh();
    }

    /**
     * Verify payment and mark it as 'lunas'.
     *
     * @throws ValidationException
     */
    public function verifyPayment(Payment $payment, User $actor, ?string $paidAt = null, ?string $notes = null): Payment
    {
        if ($payment->status === 'lunas') {
            throw ValidationException::withMessages([
                'status' => 'Pembayaran ini sudah terverifikasi lunas.',
            ]);
        }

        $this->validateStatusTransition($payment->status, 'lunas');

        $payment->update([
            'status' => 'lunas',
            'paid_at' => $paidAt ?: now(),
            'notes' => $notes !== null && $notes !== '' ? $notes : $payment->notes,
            'recorded_by' => $actor->id,
        ]);

        return $payment->fresh();
    }

    /**
     * Submit payment proof and transition status to 'menunggu_verifikasi'.
     *
     * @throws ValidationException
     */
    public function submitProof(Payment $payment, UploadedFile $proofFile, User $actor, ?string $notes = null): Payment
    {
        if ($payment->status === 'lunas') {
            throw ValidationException::withMessages([
                'status' => 'Tagihan yang sudah lunas tidak dapat mengunggah bukti pembayaran ulang.',
            ]);
        }

        $this->validateStatusTransition($payment->status, 'menunggu_verifikasi');

        $path = $proofFile->store('payment-proofs', 'public');
        $proofNote = "[Bukti Pembayaran diunggah oleh {$actor->name}: /storage/{$path}]";
        $combinedNotes = trim(($payment->notes ? "{$payment->notes}\n" : '').($notes ? "{$notes}\n" : '').$proofNote);

        $payment->update([
            'status' => 'menunggu_verifikasi',
            'notes' => $combinedNotes,
        ]);

        return $payment->fresh();
    }

    /**
     * Handle partial payment by settling paid amount and creating a remaining invoice installment.
     *
     * @return array{paid_payment: Payment, remaining_payment: ?Payment}
     *
     * @throws ValidationException
     */
    public function recordPartialPayment(
        Payment $payment,
        float $amountPaid,
        User $actor,
        ?string $paidAt = null,
        ?string $notes = null,
        ?UploadedFile $proofFile = null
    ): array {
        if ($payment->status === 'lunas') {
            throw ValidationException::withMessages([
                'status' => 'Tagihan sudah berstatus lunas.',
            ]);
        }

        if ($amountPaid <= 0) {
            throw ValidationException::withMessages([
                'amount_paid' => 'Nominal pembayaran parsial harus lebih besar dari 0.',
            ]);
        }

        if ($amountPaid > (float) $payment->amount) {
            throw ValidationException::withMessages([
                'amount_paid' => 'Nominal pembayaran tidak boleh melebihi sisa total tagihan (Rp '.number_format($payment->amount, 0, ',', '.').').',
            ]);
        }

        $paidTimestamp = $paidAt ?: now();
        $proofNote = '';
        if ($proofFile) {
            $path = $proofFile->store('payment-proofs', 'public');
            $proofNote = "\n[Bukti Pembayaran: /storage/{$path}]";
        }

        // Full payment case
        if ($amountPaid == (float) $payment->amount) {
            $updatedNotes = trim(($payment->notes ? "{$payment->notes}\n" : '').($notes ? "{$notes}" : '').$proofNote);
            $payment->update([
                'status' => 'lunas',
                'paid_at' => $paidTimestamp,
                'notes' => $updatedNotes ?: null,
                'recorded_by' => $actor->id,
            ]);

            return [
                'paid_payment' => $payment->fresh(),
                'remaining_payment' => null,
            ];
        }

        // Partial payment case: settle this invoice for amountPaid, create remaining invoice
        $remainingAmount = (float) $payment->amount - $amountPaid;
        $originalAmount = (float) $payment->amount;

        $installmentNote = 'Pembayaran Cicilan/Parsial (Rp '.number_format($amountPaid, 0, ',', '.').' dari total Rp '.number_format($originalAmount, 0, ',', '.').')';
        $combinedPaidNotes = trim(($notes ? "{$notes}\n" : '').$installmentNote.$proofNote);

        // 1. Mark current record as paid for the partial amount
        $payment->update([
            'amount' => $amountPaid,
            'status' => 'lunas',
            'paid_at' => $paidTimestamp,
            'notes' => $combinedPaidNotes,
            'recorded_by' => $actor->id,
        ]);

        // 2. Create remaining balance invoice
        $remainingStatus = ($payment->due_date && Carbon::parse($payment->due_date)->isPast()) ? 'terlambat' : 'belum_bayar';
        $remainingNotes = 'Sisa tagihan Rp '.number_format($remainingAmount, 0, ',', '.').' dari pembayaran parsial invoice #'.substr($payment->id, 0, 8);

        $remainingPayment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $payment->tenant_id,
            'branch_id' => $payment->branch_id,
            'student_id' => $payment->student_id,
            'enrollment_id' => $payment->enrollment_id,
            'period' => $payment->period.' (Sisa)',
            'amount' => $remainingAmount,
            'due_date' => $payment->due_date,
            'status' => $remainingStatus,
            'notes' => $remainingNotes,
            'recorded_by' => $actor->id,
        ]);

        return [
            'paid_payment' => $payment->fresh(),
            'remaining_payment' => $remainingPayment,
        ];
    }

    /**
     * Check and sync all overdue payments for a given tenant or entire system.
     */
    public function syncOverduePayments(?string $tenantId = null): int
    {
        $today = now()->toDateString();

        $query = Payment::where('status', 'belum_bayar')
            ->where('due_date', '<', $today);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->update(['status' => 'terlambat']);
    }

    /**
     * Validate status transition.
     *
     * @throws ValidationException
     */
    public function validateStatusTransition(string $currentStatus, string $targetStatus): void
    {
        if ($currentStatus === $targetStatus) {
            return;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        if (! in_array($targetStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Transisi status pembayaran dari '{$currentStatus}' ke '{$targetStatus}' tidak diizinkan.",
            ]);
        }
    }
}
