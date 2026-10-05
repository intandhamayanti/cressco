<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\HonorCalculation;
use App\Models\Payment;
use App\Models\TeachingSession;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OwnerNotificationService
{
    /**
     * Get executive notifications relevant for Owner oversight.
     *
     * @return Collection<int, array{id: string, type: string, title: string, message: string, time: string, is_urgent: bool, icon: string, color: string, url: string}>
     */
    public function getNotifications(Tenant $tenant): Collection
    {
        $notifications = collect();
        $now = Carbon::now();

        // 1. Pending Honor Approvals (Payroll oversight)
        $pendingHonors = HonorCalculation::where('tenant_id', $tenant->id)
            ->where('status', 'final')
            ->count();

        if ($pendingHonors > 0) {
            $notifications->push([
                'id' => 'honor-finalized',
                'type' => 'honor',
                'title' => 'Persetujuan Pembayaran Honor',
                'message' => "Ada {$pendingHonors} slip honor tutor berstatus final menunggu pencairan.",
                'time' => 'Hari ini',
                'is_urgent' => true,
                'icon' => 'credit-card',
                'color' => 'amber',
                'url' => route('owner.honors.index', ['status' => 'final']),
            ]);
        }

        // 2. Overdue Payments (Financial oversight)
        $overduePayments = Payment::where('tenant_id', $tenant->id)
            ->where('status', 'terlambat')
            ->count();

        if ($overduePayments > 0) {
            $notifications->push([
                'id' => 'payment-overdue',
                'type' => 'payment',
                'title' => 'Tagihan Siswa Terlambat',
                'message' => "Terdapat {$overduePayments} tagihan SPP siswa yang melewati tanggal jatuh tempo.",
                'time' => 'Periode berjalan',
                'is_urgent' => false,
                'icon' => 'clock',
                'color' => 'rose',
                'url' => route('owner.payments.index', ['status' => 'terlambat']),
            ]);
        }

        // 3. Low Attendance Classes / Sessions (Quality of Education oversight)
        $recentCompletedSessions = TeachingSession::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->whereDate('session_date', '>=', $now->copy()->subDays(7)->toDateString())
            ->withCount([
                'studentAttendances',
                'studentAttendances as absent_count' => function ($q) {
                    $q->whereIn('status', ['alpa', 'absent']);
                },
            ])
            ->get();

        $highAbsenteeismCount = $recentCompletedSessions->filter(function ($session) {
            return $session->student_attendances_count > 0 && ($session->absent_count / $session->student_attendances_count) >= 0.3;
        })->count();

        if ($highAbsenteeismCount > 0) {
            $notifications->push([
                'id' => 'attendance-alert',
                'type' => 'attendance',
                'title' => 'Perhatian Kehadiran Siswa',
                'message' => "Ada {$highAbsenteeismCount} sesi belajar minggu ini dengan ketidakhadiran di atas 30%.",
                'time' => '7 hari terakhir',
                'is_urgent' => false,
                'icon' => 'academic',
                'color' => 'blue',
                'url' => route('owner.attendances.index'),
            ]);
        }

        // 4. Important Administrative Audit Logs (Branch / User changes)
        $recentAudit = AuditLog::where('tenant_id', $tenant->id)
            ->whereIn('action', ['tutor_replacement.create', 'payment.verify', 'honor.finalize'])
            ->latest()
            ->limit(2)
            ->get();

        foreach ($recentAudit as $log) {
            $notifications->push([
                'id' => 'audit-'.$log->id,
                'type' => 'audit',
                'title' => 'Aktivitas Operasional',
                'message' => match ($log->action) {
                    'tutor_replacement.create' => 'Tutor pengganti ditugaskan pada salah satu sesi pengajaran.',
                    'payment.verify' => 'Admin cabang telah memverifikasi pembayaran SPP siswa.',
                    'honor.finalize' => 'Slip honor tutor telah difinalisasi.',
                    default => 'Aktivitas sistem tercatat.',
                },
                'time' => $log->created_at?->diffForHumans() ?? 'Baru saja',
                'is_urgent' => false,
                'icon' => 'notification',
                'color' => 'purple',
                'url' => route('owner.dashboard'),
            ]);
        }

        return $notifications->take(5);
    }
}
