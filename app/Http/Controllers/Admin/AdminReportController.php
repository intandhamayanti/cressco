<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\HonorCalculation;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user();
        $tenant = TenantContext::getTenant() ?? $admin->tenant;
        $tenantId = $tenant->id;

        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();
        $branches = Branch::where('tenant_id', $tenantId)
            ->whereIn('id', $accessibleBranchIds)
            ->orderBy('name')
            ->get();

        $selectedBranchId = $request->input('branch_id');
        if ($selectedBranchId && ! in_array($selectedBranchId, $accessibleBranchIds)) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }

        $branchScope = $selectedBranchId ? [$selectedBranchId] : $accessibleBranchIds;
        $selectedPeriod = $request->input('period'); // e.g. "2026-10"

        // 1. Available Periods
        $paymentPeriods = Payment::where('tenant_id', $tenantId)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->distinct()
            ->pluck('period')
            ->toArray();

        $honorPeriods = HonorCalculation::where('tenant_id', $tenantId)
            ->where(function ($q) use ($accessibleBranchIds) {
                $q->whereIn('branch_id', $accessibleBranchIds)->orWhereNull('branch_id');
            })
            ->whereNotNull('period_start')
            ->pluck('period_start')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m'))
            ->unique()
            ->toArray();

        $allPeriods = array_values(array_unique(array_filter(array_merge($paymentPeriods, $honorPeriods))));
        rsort($allPeriods);

        // 2. Base Queries
        $paymentQuery = Payment::where('tenant_id', $tenantId)->whereIn('branch_id', $branchScope);
        $honorQuery = HonorCalculation::where('tenant_id', $tenantId)->where(function ($q) use ($branchScope) {
            $q->whereIn('branch_id', $branchScope)->orWhereNull('branch_id');
        });
        $sessionQuery = TeachingSession::where('tenant_id', $tenantId)->whereIn('branch_id', $branchScope);
        $attendanceQuery = StudentAttendance::where('tenant_id', $tenantId)->whereIn('branch_id', $branchScope);

        if ($selectedPeriod) {
            $paymentQuery->where(function ($q) use ($selectedPeriod) {
                $q->where('period', $selectedPeriod)
                    ->orWhere('period', 'like', $selectedPeriod.'%');
            });
            if (str_contains($selectedPeriod, '-')) {
                [$year, $month] = explode('-', $selectedPeriod, 2);
                $honorQuery->where(function ($q) use ($year, $month) {
                    $q->where(function ($sq) use ($year, $month) {
                        $sq->whereYear('period_start', (int) $year)->whereMonth('period_start', (int) $month);
                    })->orWhere(function ($sq) use ($year, $month) {
                        $sq->whereYear('period_end', (int) $year)->whereMonth('period_end', (int) $month);
                    });
                });
                $sessionQuery->whereYear('session_date', (int) $year)->whereMonth('session_date', (int) $month);
                $attendanceQuery->whereHas('teachingSession', function ($q) use ($year, $month) {
                    $q->whereYear('session_date', (int) $year)->whereMonth('session_date', (int) $month);
                });
            }
        }

        // 3. Ringkasan Siswa
        $activeStudentsCount = Student::where('tenant_id', $tenantId)->whereIn('branch_id', $branchScope)->where('status', 'active')->count();
        $inactiveStudentsCount = Student::where('tenant_id', $tenantId)->whereIn('branch_id', $branchScope)->where('status', 'inactive')->count();
        $totalStudentsCount = $activeStudentsCount + $inactiveStudentsCount;
        $newStudentsCount = Student::where('tenant_id', $tenantId)->whereIn('branch_id', $branchScope)->where('created_at', '>=', now()->startOfMonth())->count();

        // 4. Ringkasan Kelas & Jadwal
        $activeClassesCount = Classes::where('tenant_id', $tenantId)->whereIn('branch_id', $branchScope)->where('status', 'active')->count();
        $totalCapacity = Classes::where('tenant_id', $tenantId)->whereIn('branch_id', $branchScope)->where('status', 'active')->sum('capacity');
        $activeEnrollmentsCount = Enrollment::where('tenant_id', $tenantId)
            ->whereHas('classModel', function ($q) use ($branchScope) {
                $q->whereIn('branch_id', $branchScope)->where('status', 'active');
            })
            ->where('status', 'active')
            ->count();
        $capacityUtilization = $totalCapacity > 0 ? round(($activeEnrollmentsCount / $totalCapacity) * 100, 1) : 0;
        $activeSchedulesCount = Schedule::where('tenant_id', $tenantId)
            ->whereIn('branch_id', $branchScope)
            ->where('status', 'active')
            ->count();

        // 5. Ringkasan Kehadiran
        $completedSessionsCount = (clone $sessionQuery)->where('status', 'completed')->count();
        $totalAttendances = (clone $attendanceQuery)->count();
        $presentAttendances = (clone $attendanceQuery)->whereIn('status', ['hadir', 'present'])->count();
        $permissionAttendances = (clone $attendanceQuery)->whereIn('status', ['izin', 'permission', 'excused'])->count();
        $sickAttendances = (clone $attendanceQuery)->whereIn('status', ['sakit', 'sick'])->count();
        $absentAttendances = (clone $attendanceQuery)->whereIn('status', ['alpa', 'absent'])->count();
        $attendanceRate = $totalAttendances > 0 ? round(($presentAttendances / $totalAttendances) * 100, 1) : 0;

        // 6. Ringkasan Pembayaran
        $totalInvoiced = (clone $paymentQuery)->sum('amount');
        $totalRevenue = (clone $paymentQuery)->where('status', 'lunas')->sum('amount');
        $totalOutstanding = (clone $paymentQuery)->whereIn('status', ['belum_bayar', 'menunggu_verifikasi', 'terlambat'])->sum('amount');
        $totalOverdue = (clone $paymentQuery)->where('status', 'terlambat')->sum('amount');
        $collectionRate = $totalInvoiced > 0 ? round(($totalRevenue / $totalInvoiced) * 100, 1) : 0;

        // 7. Ringkasan Honor Tutor
        $totalHonor = (clone $honorQuery)->sum('final_amount');
        $paidHonor = (clone $honorQuery)->where('status', 'paid')->sum('final_amount');
        $finalHonor = (clone $honorQuery)->where('status', 'final')->sum('final_amount');
        $draftHonor = (clone $honorQuery)->where('status', 'draft')->sum('final_amount');

        // 8. Branch Comparison (for multiple accessible branches)
        $branchComparison = [];
        foreach ($branches as $b) {
            $bStudents = Student::where('tenant_id', $tenantId)->where('branch_id', $b->id)->where('status', 'active')->count();
            $bClasses = Classes::where('tenant_id', $tenantId)->where('branch_id', $b->id)->where('status', 'active')->count();

            $bPayQuery = Payment::where('tenant_id', $tenantId)->where('branch_id', $b->id);
            if ($selectedPeriod) {
                $bPayQuery->where(function ($q) use ($selectedPeriod) {
                    $q->where('period', $selectedPeriod)
                        ->orWhere('period', 'like', $selectedPeriod.'%');
                });
            }
            $bRevenue = (clone $bPayQuery)->where('status', 'lunas')->sum('amount');
            $bOutstanding = (clone $bPayQuery)->whereIn('status', ['belum_bayar', 'menunggu_verifikasi', 'terlambat'])->sum('amount');

            $bSessions = TeachingSession::where('tenant_id', $tenantId)->where('branch_id', $b->id)->where('status', 'completed')->count();

            $branchComparison[] = [
                'id' => $b->id,
                'name' => $b->name,
                'code' => $b->code,
                'students' => $bStudents,
                'classes' => $bClasses,
                'sessions' => $bSessions,
                'revenue' => $bRevenue,
                'outstanding' => $bOutstanding,
            ];
        }

        return view('admin.reports.index', [
            'tenant' => $tenant,
            'branches' => $branches,
            'allPeriods' => $allPeriods,
            'filters' => [
                'branch_id' => $selectedBranchId,
                'period' => $selectedPeriod,
            ],
            'students' => [
                'total' => $totalStudentsCount,
                'active' => $activeStudentsCount,
                'inactive' => $inactiveStudentsCount,
                'new' => $newStudentsCount,
            ],
            'classes' => [
                'activeClasses' => $activeClassesCount,
                'capacity' => $totalCapacity,
                'enrollments' => $activeEnrollmentsCount,
                'utilization' => $capacityUtilization,
                'schedules' => $activeSchedulesCount,
            ],
            'attendance' => [
                'sessions' => $completedSessionsCount,
                'total' => $totalAttendances,
                'present' => $presentAttendances,
                'permission' => $permissionAttendances,
                'sick' => $sickAttendances,
                'absent' => $absentAttendances,
                'rate' => $attendanceRate,
            ],
            'finance' => [
                'invoiced' => $totalInvoiced,
                'revenue' => $totalRevenue,
                'outstanding' => $totalOutstanding,
                'overdue' => $totalOverdue,
                'collectionRate' => $collectionRate,
            ],
            'honor' => [
                'total' => $totalHonor,
                'paid' => $paidHonor,
                'final' => $finalHonor,
                'draft' => $draftHonor,
                'sessions' => $completedSessionsCount,
            ],
            'branchComparison' => $branchComparison,
        ]);
    }
}
