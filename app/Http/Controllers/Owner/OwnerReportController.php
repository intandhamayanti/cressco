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

class OwnerReportController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;
        $tenantId = $tenant->id;

        $branches = Branch::where('tenant_id', $tenantId)->orderBy('name')->get();

        $selectedBranchId = $request->input('branch_id');
        $selectedPeriod = $request->input('period'); // e.g. "2026-02" or null for all

        // Available periods from payments & honor calculations
        $paymentPeriods = Payment::where('tenant_id', $tenantId)
            ->distinct()
            ->pluck('period')
            ->toArray();

        $honorPeriods = HonorCalculation::where('tenant_id', $tenantId)
            ->whereNotNull('period_start')
            ->pluck('period_start')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m'))
            ->unique()
            ->toArray();

        $allPeriods = array_values(array_unique(array_filter(array_merge($paymentPeriods, $honorPeriods))));
        rsort($allPeriods);

        // 1. Base Queries
        $paymentQuery = Payment::where('tenant_id', $tenantId);
        $honorQuery = HonorCalculation::where('tenant_id', $tenantId);

        if ($selectedBranchId) {
            $paymentQuery->where('branch_id', $selectedBranchId);
            $honorQuery->where('branch_id', $selectedBranchId);
        }

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
            }
        }

        // 2. Financial Metrics
        $totalInvoiced = (clone $paymentQuery)->sum('amount');
        $totalRevenue = (clone $paymentQuery)->where('status', 'lunas')->sum('amount');
        $totalOutstanding = (clone $paymentQuery)->whereIn('status', ['belum_bayar', 'menunggu_verifikasi', 'terlambat'])->sum('amount');
        $totalOverdue = (clone $paymentQuery)->where('status', 'terlambat')->sum('amount');

        $totalExpenses = (clone $honorQuery)->whereIn('status', ['final', 'paid'])->sum('final_amount');
        $pendingExpenses = (clone $honorQuery)->where('status', 'draft')->sum('final_amount');
        $netProfit = $totalRevenue - $totalExpenses;

        $collectionRate = $totalInvoiced > 0 ? round(($totalRevenue / $totalInvoiced) * 100, 1) : 0;

        // 3. Status Distributions
        $statusCounts = [
            'lunas' => (clone $paymentQuery)->where('status', 'lunas')->count(),
            'belum_bayar' => (clone $paymentQuery)->where('status', 'belum_bayar')->count(),
            'menunggu_verifikasi' => (clone $paymentQuery)->where('status', 'menunggu_verifikasi')->count(),
            'terlambat' => (clone $paymentQuery)->where('status', 'terlambat')->count(),
        ];

        // 4. Monthly Trend (Past 6 Months)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthKey = $monthDate->format('Y-m');
            $monthLabel = $monthDate->isoFormat('MMM Y');

            $mRevQuery = Payment::where('tenant_id', $tenantId)
                ->where('period', $monthKey)
                ->where('status', 'lunas');

            $mExpQuery = HonorCalculation::where('tenant_id', $tenantId)
                ->whereYear('period_start', $monthDate->year)
                ->whereMonth('period_start', $monthDate->month)
                ->whereIn('status', ['final', 'paid']);

            if ($selectedBranchId) {
                $mRevQuery->where('branch_id', $selectedBranchId);
                $mExpQuery->where('branch_id', $selectedBranchId);
            }

            $mRev = $mRevQuery->sum('amount');
            $mExp = $mExpQuery->sum('final_amount');
            $mProfit = $mRev - $mExp;

            $monthlyTrends[] = [
                'period' => $monthKey,
                'label' => $monthLabel,
                'revenue' => (float) $mRev,
                'expenses' => (float) $mExp,
                'net_profit' => (float) $mProfit,
            ];
        }

        // 5. Branch Breakdown Comparison
        $branchBreakdown = [];
        foreach ($branches as $branch) {
            $bStudentCount = Student::where('tenant_id', $tenantId)->where('branch_id', $branch->id)->where('status', 'active')->count();

            $bPayQuery = Payment::where('tenant_id', $tenantId)->where('branch_id', $branch->id);
            $bHonQuery = HonorCalculation::where('tenant_id', $tenantId)->where('branch_id', $branch->id);

            if ($selectedPeriod) {
                $bPayQuery->where(function ($q) use ($selectedPeriod) {
                    $q->where('period', $selectedPeriod)
                        ->orWhere('period', 'like', $selectedPeriod.'%');
                });
                if (str_contains($selectedPeriod, '-')) {
                    [$year, $month] = explode('-', $selectedPeriod, 2);
                    $bHonQuery->where(function ($q) use ($year, $month) {
                        $q->where(function ($sq) use ($year, $month) {
                            $sq->whereYear('period_start', (int) $year)->whereMonth('period_start', (int) $month);
                        })->orWhere(function ($sq) use ($year, $month) {
                            $sq->whereYear('period_end', (int) $year)->whereMonth('period_end', (int) $month);
                        });
                    });
                }
            }

            $bInvoiced = (clone $bPayQuery)->sum('amount');
            $bRevenue = (clone $bPayQuery)->where('status', 'lunas')->sum('amount');
            $bOutstanding = (clone $bPayQuery)->whereIn('status', ['belum_bayar', 'menunggu_verifikasi', 'terlambat'])->sum('amount');
            $bExpenses = (clone $bHonQuery)->whereIn('status', ['final', 'paid'])->sum('final_amount');
            $bProfit = $bRevenue - $bExpenses;
            $bCollectionRate = $bInvoiced > 0 ? round(($bRevenue / $bInvoiced) * 100, 1) : 0;

            $branchBreakdown[] = [
                'id' => $branch->id,
                'name' => $branch->name,
                'city' => $branch->city,
                'active_students' => $bStudentCount,
                'invoiced' => $bInvoiced,
                'revenue' => $bRevenue,
                'outstanding' => $bOutstanding,
                'expenses' => $bExpenses,
                'net_profit' => $bProfit,
                'collection_rate' => $bCollectionRate,
            ];
        }

        return view('owner.reports.index', [
            'tenant' => $tenant,
            'branches' => $branches,
            'periods' => $allPeriods,
            'selectedBranchId' => $selectedBranchId,
            'selectedPeriod' => $selectedPeriod,
            'metrics' => [
                'totalInvoiced' => $totalInvoiced,
                'totalRevenue' => $totalRevenue,
                'totalOutstanding' => $totalOutstanding,
                'totalOverdue' => $totalOverdue,
                'totalExpenses' => $totalExpenses,
                'pendingExpenses' => $pendingExpenses,
                'netProfit' => $netProfit,
                'collectionRate' => $collectionRate,
            ],
            'statusCounts' => $statusCounts,
            'monthlyTrends' => $monthlyTrends,
            'branchBreakdown' => $branchBreakdown,
        ]);
    }
}
