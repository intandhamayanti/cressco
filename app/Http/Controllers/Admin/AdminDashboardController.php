<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        // 1. Accessible branches for this Admin
        $accessibleBranches = $user->accessibleBranches();
        $accessibleBranchIds = $user->accessibleBranchIds()->all();

        // 2. Branch filter context
        $selectedBranchId = $request->query('branch_id');
        $selectedBranch = null;

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            if (in_array($selectedBranchId, $accessibleBranchIds, true)) {
                $selectedBranch = $accessibleBranches->firstWhere('id', $selectedBranchId);
            } else {
                $selectedBranchId = null; // Fallback if admin tries to access unauthorized branch
            }
        } else {
            $selectedBranchId = null;
        }

        $effectiveBranchIds = $selectedBranchId ? [$selectedBranchId] : $accessibleBranchIds;

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 3. Operational Overview KPIs (Strictly scoped to Admin's branch access)
        // Active Students
        $activeStudentsCount = Student::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->where('status', 'active')
            ->count();

        $totalStudentsCount = Student::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->count();

        // Active Classes
        $activeClassesCount = Classes::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->where('status', 'active')
            ->count();

        // Today's Sessions
        $todaySessionsQuery = TeachingSession::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->whereDate('session_date', $now->toDateString());

        $todaySessionsCount = (clone $todaySessionsQuery)->count();
        $todayCompletedSessionsCount = (clone $todaySessionsQuery)->where('status', 'completed')->count();

        // Today's Teaching Sessions List
        $todaySessions = TeachingSession::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->whereDate('session_date', $now->toDateString())
            ->with(['classModel', 'scheduledTutor', 'actualTutor', 'branch'])
            ->orderBy('start_time')
            ->get();

        // Attendance Rate (This Month)
        $attendanceQuery = StudentAttendance::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->whereHas('teachingSession', function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('session_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            });

        $totalAttendanceCount = (clone $attendanceQuery)->count();
        $presentAttendanceCount = (clone $attendanceQuery)->whereIn('status', ['hadir', 'present'])->count();
        $attendanceRate = $totalAttendanceCount > 0 ? round(($presentAttendanceCount / $totalAttendanceCount) * 100) : 0;

        // Pending / Overdue Payments needing operational action
        $pendingPaymentsQuery = Payment::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->whereIn('status', ['menunggu_verifikasi', 'terlambat', 'belum_bayar']);

        $pendingPaymentsCount = (clone $pendingPaymentsQuery)->count();
        $pendingPaymentsAmount = (float) (clone $pendingPaymentsQuery)->sum('amount');

        $actionRequiredPayments = Payment::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->whereIn('status', ['menunggu_verifikasi', 'terlambat'])
            ->with(['student', 'branch'])
            ->latest()
            ->limit(5)
            ->get();

        // Recent Operational Activities (Enrollments & Payments)
        $recentEnrollments = Enrollment::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->with(['student', 'classModel', 'branch'])
            ->latest()
            ->limit(4)
            ->get()
            ->map(function ($e) {
                return [
                    'time' => $e->created_at->diffForHumans(),
                    'timestamp_label' => $e->created_at->translatedFormat('d M, H:i'),
                    'user' => $e->student?->name ?? 'Siswa Baru',
                    'action' => 'Pendaftaran kelas '.($e->classModel?->name ?? 'Bimbel'),
                    'detail' => $e->branch?->name ?? 'Cabang',
                    'type' => 'enrollment',
                    'badge' => 'Enrollment',
                    'created_at' => $e->created_at,
                ];
            });

        $recentPayments = Payment::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->with(['student', 'branch'])
            ->latest()
            ->limit(4)
            ->get()
            ->map(function ($p) {
                return [
                    'time' => $p->created_at->diffForHumans(),
                    'timestamp_label' => $p->created_at->translatedFormat('d M, H:i'),
                    'user' => $p->student?->name ?? 'Siswa',
                    'action' => $p->status === 'lunas' ? 'Pembayaran lunas' : 'Tagihan baru',
                    'detail' => 'Rp '.number_format($p->amount, 0, ',', '.').' • '.($p->branch?->name ?? 'Cabang'),
                    'type' => $p->status === 'lunas' ? 'payment_success' : 'payment_pending',
                    'badge' => strtoupper($p->status),
                    'created_at' => $p->created_at,
                ];
            });

        $recentActivities = $recentEnrollments->concat($recentPayments)
            ->sortByDesc('created_at')
            ->values()
            ->take(6);

        return view('admin.dashboard', compact(
            'tenant',
            'accessibleBranches',
            'selectedBranch',
            'selectedBranchId',
            'activeStudentsCount',
            'totalStudentsCount',
            'activeClassesCount',
            'todaySessionsCount',
            'todayCompletedSessionsCount',
            'todaySessions',
            'attendanceRate',
            'totalAttendanceCount',
            'pendingPaymentsCount',
            'pendingPaymentsAmount',
            'actionRequiredPayments',
            'recentActivities'
        ));
    }
}
