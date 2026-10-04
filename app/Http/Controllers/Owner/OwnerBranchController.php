<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreBranchRequest;
use App\Http\Requests\Owner\UpdateBranchRequest;
use App\Models\Branch;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OwnerBranchController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        $search = $request->query('search');
        $status = $request->query('status', 'all');

        $query = Branch::where('tenant_id', $tenant->id)
            ->withCount(['students', 'classes', 'branchUsers'])
            ->with(['users' => function ($q) use ($tenant) {
                $q->where('users.tenant_id', $tenant->id);
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $branches = $query->orderBy('name')->get();

        // Overall stats
        $totalBranchesCount = Branch::where('tenant_id', $tenant->id)->count();
        $activeBranchesCount = Branch::where('tenant_id', $tenant->id)->where('status', 'active')->count();
        $inactiveBranchesCount = $totalBranchesCount - $activeBranchesCount;

        return view('owner.branches.index', compact(
            'tenant',
            'branches',
            'search',
            'status',
            'totalBranchesCount',
            'activeBranchesCount',
            'inactiveBranchesCount'
        ));
    }

    public function show(Request $request, Branch $branch): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $branch->tenant_id !== $tenant->id) {
            abort(404, 'Branch not found in this tenant.');
        }

        // Authorize with policy
        Gate::authorize('view', $branch);

        $branch->load([
            'users' => function ($q) use ($tenant) {
                $q->where('users.tenant_id', $tenant->id);
            },
            'classes' => function ($q) use ($tenant) {
                $q->where('classes.tenant_id', $tenant->id)
                    ->withCount('enrollments');
            },
        ]);

        $branch->loadCount(['students', 'classes', 'branchUsers', 'tutorAssignments']);

        $recentStudents = $branch->students()
            ->where('tenant_id', $tenant->id)
            ->latest('joined_at')
            ->limit(5)
            ->get();

        return view('owner.branches.show', compact('tenant', 'branch', 'recentStudents'));
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('create', Branch::class);

        $validated = $request->validated();

        // Auto-generate code if left blank
        if (empty($validated['code'])) {
            $words = explode(' ', $validated['name']);
            $initials = '';
            foreach ($words as $w) {
                if (! empty($w)) {
                    $initials .= strtoupper(substr($w, 0, 1));
                }
            }
            $validated['code'] = substr($initials ?: 'CBG', 0, 4).'-'.rand(10, 99);
        }

        $branch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        return redirect()
            ->route('owner.branches.index')
            ->with('success', "Cabang '{$branch->name}' berhasil ditambahkan.");
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $branch->tenant_id !== $tenant->id) {
            abort(404, 'Branch not found in this tenant.');
        }

        Gate::authorize('update', $branch);

        $validated = $request->validated();

        $branch->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? $branch->code,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->back()
            ->with('success', "Data cabang '{$branch->name}' berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, Branch $branch): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $branch->tenant_id !== $tenant->id) {
            abort(404, 'Branch not found in this tenant.');
        }

        Gate::authorize('update', $branch);

        $newStatus = $branch->status === 'active' ? 'inactive' : 'active';
        $branch->update(['status' => $newStatus]);

        $statusLabel = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Cabang '{$branch->name}' berhasil {$statusLabel}.");
    }
}
