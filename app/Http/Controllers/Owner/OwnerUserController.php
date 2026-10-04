<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreUserRequest;
use App\Http\Requests\Owner\UpdateUserRequest;
use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OwnerUserController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', User::class);

        $search = $request->query('search');
        $role = $request->query('role', 'all');
        $status = $request->query('status', 'all');
        $branchId = $request->query('branch_id', 'all');

        $query = User::where('tenant_id', $tenant->id)
            ->with(['branches' => function ($q) use ($tenant) {
                $q->where('branches.tenant_id', $tenant->id);
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($branchId && $branchId !== 'all') {
            $query->where(function ($q) use ($branchId) {
                $q->whereHas('branches', fn ($sq) => $sq->where('branches.id', $branchId))
                    ->orWhereHas('tutorAssignments', fn ($sq) => $sq->where('branch_id', $branchId));
            });
        }

        $users = $query->orderByRaw("CASE WHEN role = 'owner' THEN 1 WHEN role = 'admin' THEN 2 WHEN role = 'tutor' THEN 3 ELSE 4 END")->orderBy('name')->get();

        // Metrics
        $totalUsersCount = User::where('tenant_id', $tenant->id)->count();
        $adminCount = User::where('tenant_id', $tenant->id)->where('role', 'admin')->count();
        $tutorCount = User::where('tenant_id', $tenant->id)->where('role', 'tutor')->count();
        $activeCount = User::where('tenant_id', $tenant->id)->where('status', 'active')->count();
        $inactiveCount = $totalUsersCount - $activeCount;

        $branches = Branch::where('tenant_id', $tenant->id)->orderBy('name')->get();

        return view('owner.users.index', compact(
            'tenant',
            'users',
            'branches',
            'search',
            'role',
            'status',
            'branchId',
            'totalUsersCount',
            'adminCount',
            'tutorCount',
            'activeCount',
            'inactiveCount'
        ));
    }

    public function show(Request $request, User $user): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $user->tenant_id !== $tenant->id) {
            abort(404, 'User not found in this tenant.');
        }

        Gate::authorize('view', $user);

        $user->load([
            'branches' => function ($q) use ($tenant) {
                $q->where('branches.tenant_id', $tenant->id);
            },
            'tutorAssignments' => function ($q) use ($tenant) {
                $q->where('tutor_assignments.tenant_id', $tenant->id)
                    ->with(['branch', 'classModel']);
            },
        ]);

        $branches = Branch::where('tenant_id', $tenant->id)->orderBy('name')->get();

        return view('owner.users.show', compact('tenant', 'user', 'branches'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        $validated = $request->validated();

        Gate::authorize('create', [User::class, $validated['role']]);

        $password = ! empty($validated['password']) ? $validated['password'] : Str::password(16);

        $newUser = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'role' => $validated['role'],
            'status' => $validated['status'] ?? 'active',
            'email_verified_at' => now(),
        ]);

        // Assign branch access for Admin
        if ($validated['role'] === 'admin' && ! empty($validated['branch_ids'])) {
            foreach ($validated['branch_ids'] as $bId) {
                BranchUser::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant->id,
                    'branch_id' => $bId,
                    'user_id' => $newUser->id,
                ]);
            }
        }

        return redirect()
            ->route('owner.users.index')
            ->with('success', "Pengguna '{$newUser->name}' ({$newUser->role}) berhasil ditambahkan.");
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $user->tenant_id !== $tenant->id) {
            abort(404, 'User not found in this tenant.');
        }

        Gate::authorize('update', $user);

        $validated = $request->validated();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Sync branch access for Admin
        if ($validated['role'] === 'admin') {
            BranchUser::where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->delete();

            if (! empty($validated['branch_ids'])) {
                foreach ($validated['branch_ids'] as $bId) {
                    BranchUser::create([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenant->id,
                        'branch_id' => $bId,
                        'user_id' => $user->id,
                    ]);
                }
            }
        } elseif ($validated['role'] === 'tutor') {
            // Tutors don't use branch_user table (they use tutor_assignments)
            BranchUser::where('tenant_id', $tenant->id)
                ->where('user_id', $user->id)
                ->delete();
        }

        return redirect()
            ->back()
            ->with('success', "Data pengguna '{$user->name}' berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $user->tenant_id !== $tenant->id) {
            abort(404, 'User not found in this tenant.');
        }

        if ($user->id === $request->user()->id) {
            return redirect()
                ->back()
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        Gate::authorize('update', $user);

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        $statusLabel = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Status pengguna '{$user->name}' berhasil {$statusLabel}.");
    }
}
