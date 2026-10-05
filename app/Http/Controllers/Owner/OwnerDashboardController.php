<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\HonorCalculation;
use App\Models\Payment;
use App\Models\Student;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        // 1. Branches for context selector
        $branches = Branch::where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        $selectedBranchId = $request->query('branch_id');
        $selectedBranch = null;

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $selectedBranch = $branches->firstWhere('id', $selectedBranchId);
            if (! $selectedBranch) {
                $selectedBranchId = null; // Fallback to all branches
            }
        } else {
            $selectedBranchId = null;
        }

        // 2. Period filtering
        $period = $request->query('period', 'this_month');
        $now = Carbon::now();

        switch ($period) {
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                $prevStartDate = $now->copy()->subMonths(2)->startOfMonth();
                $prevEndDate = $now->copy()->subMonths(2)->endOfMonth();
                $periodLabel = 'Bulan Lalu ('.$startDate->translatedFormat('F Y').')';
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $prevStartDate = $now->copy()->subYear()->startOfYear();
                $prevEndDate = $now->copy()->subYear()->endOfYear();
                $periodLabel = 'Tahun Ini ('.$startDate->year.')';
                break;
            case 'this_month':
            default:
                $period = 'this_month';
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $prevStartDate = $now->copy()->subMonth()->startOfMonth();
                $prevEndDate = $now->copy()->subMonth()->endOfMonth();
                $periodLabel = 'Bulan Ini ('.$startDate->translatedFormat('F Y').')';
                break;
        }

        // Base query scoping closure
        $applyBranch = function ($query) use ($selectedBranchId) {
            if ($selectedBranchId) {
                $query->where('branch_id', $selectedBranchId);
            }
        };

        // 3. Business Overview KPIs
        // Active Students
        $studentsQuery = Student::where('tenant_id', $tenant->id)->where('status', 'active');
        $applyBranch($studentsQuery);
        $totalStudents = $studentsQuery->count();

        $newStudentsCount = Student::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->whereBetween('joined_at', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId))
            ->count();

        // Revenue (Paid payments in period)
        $revenueQuery = Payment::where('tenant_id', $tenant->id)
            ->where('status', 'lunas')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('paid_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->whereNull('paid_at')
                            ->whereBetween('created_at', [$startDate->toDateTimeString(), $endDate->toDateTimeString()]);
                    });
            });
        $applyBranch($revenueQuery);
        $revenue = (float) $revenueQuery->sum('amount');

        // Previous Revenue for comparison trend
        $prevRevenueQuery = Payment::where('tenant_id', $tenant->id)
            ->where('status', 'lunas')
            ->where(function ($q) use ($prevStartDate, $prevEndDate) {
                $q->whereBetween('paid_at', [$prevStartDate->toDateTimeString(), $prevEndDate->toDateTimeString()])
                    ->orWhere(function ($q2) use ($prevStartDate, $prevEndDate) {
                        $q2->whereNull('paid_at')
                            ->whereBetween('created_at', [$prevStartDate->toDateTimeString(), $prevEndDate->toDateTimeString()]);
                    });
            });
        $applyBranch($prevRevenueQuery);
        $prevRevenue = (float) $prevRevenueQuery->sum('amount');

        $revenueGrowth = $prevRevenue > 0
            ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1)
            : ($revenue > 0 ? 100 : 0);

        // Outstanding Payments (belum_bayar, menunggu_verifikasi, terlambat)
        $outstandingQuery = Payment::where('tenant_id', $tenant->id)
            ->whereIn('status', ['belum_bayar', 'menunggu_verifikasi', 'terlambat']);
        $applyBranch($outstandingQuery);
        $outstanding = (float) $outstandingQuery->sum('amount');

        // Expenses (Honor calculations finalized or paid in period)
        $expensesQuery = HonorCalculation::where('tenant_id', $tenant->id)
            ->whereIn('status', ['final', 'paid'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('period_start', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('period_end', [$startDate->toDateString(), $endDate->toDateString()]);
            });
        if ($selectedBranchId) {
            $expensesQuery->where('branch_id', $selectedBranchId);
        }
        $expenses = (float) $expensesQuery->sum('final_amount');

        $estimatedProfit = max(0, $revenue - $expenses);

        // 4. Revenue vs Expenses Chart Data (Elapsed Months up to Current Month)
        $yearStart = $now->copy()->startOfYear();
        $monthlyChartData = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $maxMonth = ($yearStart->year === $now->year) ? $now->month : 12;
        $chartYear = $yearStart->year;
        $chartRangeStart = Carbon::create($chartYear, 1, 1)->startOfMonth();
        $chartRangeEnd = Carbon::create($chartYear, $maxMonth, 1)->endOfMonth();

        // Aggregated payments in memory
        $monthlyRevenue = array_fill(1, $maxMonth, 0.0);
        $chartRevenueQuery = Payment::where('tenant_id', $tenant->id)
            ->where('status', 'lunas')
            ->where(function ($q) use ($chartRangeStart, $chartRangeEnd) {
                $q->whereBetween('paid_at', [$chartRangeStart->toDateTimeString(), $chartRangeEnd->toDateTimeString()])
                    ->orWhere(function ($q2) use ($chartRangeStart, $chartRangeEnd) {
                        $q2->whereNull('paid_at')
                            ->whereBetween('created_at', [$chartRangeStart->toDateTimeString(), $chartRangeEnd->toDateTimeString()]);
                    });
            });
        $applyBranch($chartRevenueQuery);
        foreach ($chartRevenueQuery->get(['amount', 'paid_at', 'created_at']) as $chartPayment) {
            $monthlyRevenue[(int) Carbon::parse($chartPayment->paid_at ?? $chartPayment->created_at)->month] += (float) $chartPayment->amount;
        }

        $monthlyExpenses = array_fill(1, $maxMonth, 0.0);
        $chartExpenseQuery = HonorCalculation::where('tenant_id', $tenant->id)
            ->whereIn('status', ['final', 'paid'])
            ->where(function ($q) use ($chartRangeStart, $chartRangeEnd) {
                $q->whereBetween('period_start', [$chartRangeStart->toDateString(), $chartRangeEnd->toDateString()])
                    ->orWhereBetween('period_end', [$chartRangeStart->toDateString(), $chartRangeEnd->toDateString()]);
            });
        if ($selectedBranchId) {
            $chartExpenseQuery->where('branch_id', $selectedBranchId);
        }
        foreach ($chartExpenseQuery->get(['final_amount', 'period_start', 'period_end']) as $chartHonor) {
            $honorMonths = [];
            foreach ([$chartHonor->period_start, $chartHonor->period_end] as $honorDate) {
                if ($honorDate === null) {
                    continue;
                }
                $honorDate = Carbon::parse($honorDate);
                if ($honorDate->year === $chartYear && $honorDate->month <= $maxMonth) {
                    $honorMonths[$honorDate->month] = true;
                }
            }
            foreach (array_keys($honorMonths) as $honorMonth) {
                $monthlyExpenses[$honorMonth] += (float) $chartHonor->final_amount;
            }
        }

        for ($m = 1; $m <= $maxMonth; $m++) {
            $mRev = $monthlyRevenue[$m];
            $mExp = $monthlyExpenses[$m];

            // Amounts in millions for clean chart representation
            $revInM = round($mRev / 1000000, 2);
            $expInM = round($mExp / 1000000, 2);
            $profInM = max(0, round(($mRev - $mExp) / 1000000, 2));

            $monthlyChartData[] = [
                'label' => $monthNames[$m - 1],
                'month_num' => $m,
                'val1' => $revInM,        // Revenue (millions)
                'val2' => $expInM,        // Expenses (millions)
                'val3' => $profInM,       // Profit (millions)
                'raw_revenue' => $mRev,
                'raw_expenses' => $mExp,
                'raw_profit' => max(0, $mRev - $mExp),
            ];
        }

        // 5. Payment Overview (Status counts & totals)
        $paymentStatusQuery = Payment::where('tenant_id', $tenant->id);
        $applyBranch($paymentStatusQuery);
        $paymentStatusTotals = $paymentStatusQuery
            ->selectRaw('status, count(*) as total_count, sum(amount) as total_amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $statusCount = fn (array $statuses): int => (int) $paymentStatusTotals->only($statuses)->sum('total_count');
        $statusAmount = fn (array $statuses): float => (float) $paymentStatusTotals->only($statuses)->sum('total_amount');

        $paidCount = $statusCount(['lunas']);
        $paidAmount = $statusAmount(['lunas']);
        $pendingCount = $statusCount(['belum_bayar', 'menunggu_verifikasi']);
        $pendingAmount = $statusAmount(['belum_bayar', 'menunggu_verifikasi']);
        $overdueCount = $statusCount(['terlambat']);
        $overdueAmount = $statusAmount(['terlambat']);

        return view('owner.dashboard', compact(
            'tenant',
            'branches',
            'selectedBranch',
            'selectedBranchId',
            'period',
            'periodLabel',
            'totalStudents',
            'newStudentsCount',
            'revenue',
            'revenueGrowth',
            'outstanding',
            'expenses',
            'estimatedProfit',
            'monthlyChartData',
            'paidCount',
            'paidAmount',
            'pendingCount',
            'pendingAmount',
            'overdueCount',
            'overdueAmount'
        ));
    }
}
