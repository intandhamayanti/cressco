<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HonorCalculation;
use App\Models\TeachingSession;
use App\Models\User;
use App\Services\HonorCalculationService;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminHonorController extends Controller
{
    public function __construct(
        protected HonorCalculationService $honorService
    ) {}

    public function index(Request $request): View
    {
        $admin = $request->user();
        $tenant = TenantContext::getTenant() ?? $admin->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', HonorCalculation::class);

        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();

        $search = $request->query('search');
        $tutorId = $request->query('tutor_id', 'all');
        $period = $request->query('period');

        $query = HonorCalculation::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($accessibleBranchIds) {
                $q->whereIn('branch_id', $accessibleBranchIds)
                    ->orWhereNull('branch_id');
            })
            ->with(['tutor', 'honorScheme', 'branch', 'calculatedBy', 'finalizedBy']);

        if ($search) {
            $query->whereHas('tutor', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($tutorId && $tutorId !== 'all') {
            $query->where('tutor_id', $tutorId);
        }

        if ($period) {
            $query->where(function ($q) use ($period) {
                $q->where('period_start', 'like', "{$period}%")
                    ->orWhere('period_end', 'like', "{$period}%");
            });
        }

        $calculations = $query->latest('period_start')->get();

        // Operational metrics scoped to accessible branches
        $baseCalcQuery = HonorCalculation::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($accessibleBranchIds) {
                $q->whereIn('branch_id', $accessibleBranchIds)
                    ->orWhereNull('branch_id');
            });

        $totalEstimatedHonor = (clone $baseCalcQuery)->sum('final_amount');
        $totalCompletedSessions = TeachingSession::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'completed')
            ->count();

        $tutors = User::where('tenant_id', $tenant->id)
            ->where('role', 'tutor')
            ->where('status', 'active')
            ->whereHas('branches', function ($q) use ($accessibleBranchIds) {
                $q->whereIn('branches.id', $accessibleBranchIds);
            })
            ->orderBy('name')
            ->get();

        $periods = HonorCalculation::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($accessibleBranchIds) {
                $q->whereIn('branch_id', $accessibleBranchIds)
                    ->orWhereNull('branch_id');
            })
            ->whereNotNull('period_start')
            ->orderByDesc('period_start')
            ->pluck('period_start')
            ->map(fn ($date) => $date ? Carbon::parse($date)->format('Y-m') : null)
            ->filter()
            ->unique()
            ->values();

        $totalActiveTutors = $calculations->pluck('tutor_id')->unique()->count();

        return view('admin.honors.index', compact(
            'tenant',
            'calculations',
            'tutors',
            'periods',
            'totalActiveTutors',
            'totalCompletedSessions',
            'totalEstimatedHonor',
            'search',
            'tutorId',
            'period'
        ));
    }

    public function show(Request $request, HonorCalculation $calculation): View
    {
        $admin = $request->user();
        $tenant = TenantContext::getTenant() ?? $admin->tenant;

        if (! $tenant || $calculation->tenant_id !== $tenant->id) {
            abort(404, 'Data perhitungan honor tidak ditemukan.');
        }

        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();
        if ($calculation->branch_id !== null && ! in_array($calculation->branch_id, $accessibleBranchIds)) {
            abort(403, 'Anda tidak memiliki akses ke data honor cabang ini.');
        }

        Gate::authorize('view', $calculation);

        $calculation->load(['tutor', 'honorScheme', 'branch', 'calculatedBy', 'finalizedBy']);

        // Load actual teaching sessions where this tutor was the actual tutor (or scheduled tutor when no replacement)
        $sessionsQuery = TeachingSession::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($calculation) {
                $q->where('actual_tutor_id', $calculation->tutor_id)
                    ->orWhere(function ($sq) use ($calculation) {
                        $sq->whereNull('actual_tutor_id')
                            ->where('scheduled_tutor_id', $calculation->tutor_id);
                    });
            })
            ->whereBetween('session_date', [$calculation->period_start, $calculation->period_end])
            ->where('status', 'completed')
            ->whereIn('branch_id', $accessibleBranchIds)
            ->with(['classModel.branch', 'scheduledTutor', 'actualTutor'])
            ->orderBy('session_date')
            ->orderBy('start_time');

        if ($calculation->branch_id) {
            $sessionsQuery->where('branch_id', $calculation->branch_id);
        }

        $sessions = $sessionsQuery->get();

        $ratePerSession = $calculation->honorScheme?->rate ?? ($calculation->total_sessions > 0 ? (float) ($calculation->base_amount / $calculation->total_sessions) : 0);

        return view('admin.honors.show', compact('tenant', 'calculation', 'sessions', 'ratePerSession'));
    }

    public function calculate(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $tenant = TenantContext::getTenant() ?? $admin->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('create', HonorCalculation::class);

        $validated = $request->validate([
            'tutor_id' => ['nullable', 'uuid'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'branch_id' => ['nullable', 'uuid'],
        ]);

        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();
        if (! empty($validated['branch_id']) && ! in_array($validated['branch_id'], $accessibleBranchIds)) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }

        if (! empty($validated['tutor_id'])) {
            $tutor = User::where('tenant_id', $tenant->id)->where('id', $validated['tutor_id'])->firstOrFail();
            $this->honorService->calculateForTutor(
                $tutor,
                $validated['period_start'],
                $validated['period_end'],
                $admin,
                $validated['branch_id'] ?? null
            );
        } else {
            $this->honorService->calculateForTenant(
                $tenant->id,
                $validated['period_start'],
                $validated['period_end'],
                $admin,
                $accessibleBranchIds
            );
        }

        return redirect()->route('admin.honors.index')->with('success', 'Perhitungan honor berhasil dijalankan.');
    }

    public function finalize(Request $request, HonorCalculation $calculation): RedirectResponse
    {
        $admin = $request->user();
        $tenant = TenantContext::getTenant() ?? $admin->tenant;

        if (! $tenant || $calculation->tenant_id !== $tenant->id) {
            abort(404, 'Data perhitungan honor tidak ditemukan.');
        }

        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();
        if ($calculation->branch_id !== null && ! in_array($calculation->branch_id, $accessibleBranchIds)) {
            abort(403, 'Anda tidak memiliki akses ke data honor cabang ini.');
        }

        Gate::authorize('update', $calculation);

        $validated = $request->validate([
            'adjustment_amount' => ['nullable', 'numeric'],
            'adjustment_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->honorService->finalizeCalculation(
            $calculation,
            $admin,
            isset($validated['adjustment_amount']) ? (float) $validated['adjustment_amount'] : null,
            $validated['adjustment_reason'] ?? null
        );

        return back()->with('success', "Perhitungan honor {$calculation->tutor?->name} berhasil difinalisasi.");
    }

    public function markPaid(Request $request, HonorCalculation $calculation): RedirectResponse
    {
        $admin = $request->user();
        $tenant = TenantContext::getTenant() ?? $admin->tenant;

        if (! $tenant || $calculation->tenant_id !== $tenant->id) {
            abort(404, 'Data perhitungan honor tidak ditemukan.');
        }

        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();
        if ($calculation->branch_id !== null && ! in_array($calculation->branch_id, $accessibleBranchIds)) {
            abort(403, 'Anda tidak memiliki akses ke data honor cabang ini.');
        }

        Gate::authorize('update', $calculation);

        $this->honorService->markPaid($calculation);

        return back()->with('success', "Honor tutor {$calculation->tutor?->name} sebesar Rp ".number_format($calculation->final_amount, 0, ',', '.').' berhasil ditandai telah dibayar (Lunas).');
    }
}
