<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignTutorClassRequest;
use App\Http\Requests\Admin\StoreTutorRequest;
use App\Http\Requests\Admin\UpdateTutorRequest;
use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Models\TutorAssignment;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminTutorController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', User::class);

        $accessibleBranches = $user->accessibleBranches();
        $accessibleBranchIds = $user->accessibleBranchIds()->all();

        $search = $request->query('search');
        $branchId = $request->query('branch_id', 'all');
        $status = $request->query('status', 'all');

        $query = User::where('tenant_id', $tenant->id)
            ->where('role', 'tutor')
            ->where(function ($q) use ($accessibleBranchIds) {
                $q->whereHas('branches', fn ($sq) => $sq->whereIn('branches.id', $accessibleBranchIds))
                    ->orWhereHas('tutorAssignments', fn ($sq) => $sq->whereIn('branch_id', $accessibleBranchIds))
                    ->orWhereHas('actualTeachingSessions', fn ($sq) => $sq->whereIn('branch_id', $accessibleBranchIds))
                    ->orWhereHas('scheduledSchedules', fn ($sq) => $sq->whereIn('branch_id', $accessibleBranchIds))
                    ->orDoesntHave('branches'); // Tutors registered in tenant without branch assigned yet
            })
            ->with([
                'tutorAssignments' => function ($q) use ($tenant, $accessibleBranchIds) {
                    $q->where('tenant_id', $tenant->id)
                        ->whereIn('branch_id', $accessibleBranchIds)
                        ->with('class.branch');
                },
                'scheduledSchedules' => function ($q) use ($tenant, $accessibleBranchIds) {
                    $q->where('tenant_id', $tenant->id)
                        ->whereIn('branch_id', $accessibleBranchIds)
                        ->with('class.branch');
                },
                'actualTeachingSessions' => function ($q) use ($tenant, $accessibleBranchIds) {
                    $q->where('tenant_id', $tenant->id)
                        ->whereIn('branch_id', $accessibleBranchIds)
                        ->where('status', 'completed');
                },
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($branchId && $branchId !== 'all') {
            if (in_array($branchId, $accessibleBranchIds, true)) {
                $query->where(function ($q) use ($branchId) {
                    $q->whereHas('branches', fn ($sq) => $sq->where('branches.id', $branchId))
                        ->orWhereHas('tutorAssignments', fn ($sq) => $sq->where('branch_id', $branchId))
                        ->orWhereHas('actualTeachingSessions', fn ($sq) => $sq->where('branch_id', $branchId))
                        ->orWhereHas('scheduledSchedules', fn ($sq) => $sq->where('branch_id', $branchId));
                });
            }
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $tutors = $query->orderBy('name')->get();

        // Operational Metrics (scoped to Admin's branch context)
        $totalTutorsCount = $tutors->count();
        $activeTutorsCount = $tutors->where('status', 'active')->count();
        $inactiveTutorsCount = $totalTutorsCount - $activeTutorsCount;

        $totalClassAssignmentsCount = TutorAssignment::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'active')
            ->count();

        $totalCompletedSessionsCount = TeachingSession::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'completed')
            ->count();

        $classes = Classes::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'active')
            ->with('branch')
            ->orderBy('name')
            ->get();

        return view('admin.tutors.index', compact(
            'tenant',
            'tutors',
            'accessibleBranches',
            'classes',
            'totalTutorsCount',
            'activeTutorsCount',
            'inactiveTutorsCount',
            'totalClassAssignmentsCount',
            'totalCompletedSessionsCount',
            'search',
            'branchId',
            'status'
        ));
    }

    public function store(StoreTutorRequest $request): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('create', [User::class, 'tutor']);

        $validated = $request->validated();

        $tutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => 'tutor',
            'status' => $validated['status'] ?? 'active',
            'password' => Hash::make($validated['password'] ?? Str::password(16)),
            'email_verified_at' => now(),
        ]);

        // Branch Association
        if (! empty($validated['branch_id'])) {
            BranchUser::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_id' => $validated['branch_id'],
                'user_id' => $tutor->id,
            ]);
        }

        // Optional Class Assignment
        if (! empty($validated['class_id'])) {
            $class = Classes::find($validated['class_id']);
            if ($class && $class->tenant_id === $tenant->id && $user->hasBranchAccess($class->branch_id)) {
                TutorAssignment::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant->id,
                    'branch_id' => $class->branch_id,
                    'tutor_id' => $tutor->id,
                    'class_id' => $class->id,
                    'started_at' => now()->toDateString(),
                    'status' => 'active',
                ]);
            }
        }

        return redirect()->route('admin.tutors.show', $tutor)
            ->with('success', "Tutor '{$tutor->name}' berhasil didaftarkan.");
    }

    public function show(Request $request, User $tutor): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $tutor->tenant_id !== $tenant->id || $tutor->role !== 'tutor') {
            abort(404, 'Data tutor tidak ditemukan.');
        }

        Gate::authorize('view', $tutor);

        $accessibleBranches = $user->accessibleBranches();
        $accessibleBranchIds = $user->accessibleBranchIds()->all();

        $tutor->load([
            'branches' => function ($q) use ($tenant) {
                $q->where('branches.tenant_id', $tenant->id);
            },
            'tutorAssignments' => function ($q) use ($tenant, $accessibleBranchIds) {
                $q->where('tenant_id', $tenant->id)
                    ->whereIn('branch_id', $accessibleBranchIds)
                    ->with('class.branch')
                    ->latest();
            },
            'scheduledSchedules' => function ($q) use ($tenant, $accessibleBranchIds) {
                $q->where('tenant_id', $tenant->id)
                    ->whereIn('branch_id', $accessibleBranchIds)
                    ->with('class.branch')
                    ->orderBy('day_of_week')
                    ->orderBy('start_time');
            },
            'actualTeachingSessions' => function ($q) use ($tenant, $accessibleBranchIds) {
                $q->where('tenant_id', $tenant->id)
                    ->whereIn('branch_id', $accessibleBranchIds)
                    ->with(['classModel.branch', 'scheduledTutor'])
                    ->withCount('studentAttendances')
                    ->latest('session_date')
                    ->limit(20);
            },
        ]);

        // Operational stats for this tutor in accessible branches
        $activeClassesCount = $tutor->tutorAssignments->where('status', 'active')->count();
        $weeklySchedulesCount = $tutor->scheduledSchedules->where('status', 'active')->count();
        $completedSessionsCount = TeachingSession::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('actual_tutor_id', $tutor->id)
            ->where('status', 'completed')
            ->count();

        $totalAttendancesCount = StudentAttendance::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('recorded_by', $tutor->id)
            ->count();

        $classes = Classes::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'active')
            ->with('branch')
            ->orderBy('name')
            ->get();

        return view('admin.tutors.show', compact(
            'tenant',
            'tutor',
            'accessibleBranches',
            'classes',
            'activeClassesCount',
            'weeklySchedulesCount',
            'completedSessionsCount',
            'totalAttendancesCount'
        ));
    }

    public function update(UpdateTutorRequest $request, User $tutor): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $tutor->tenant_id !== $tenant->id || $tutor->role !== 'tutor') {
            abort(404, 'Data tutor tidak ditemukan.');
        }

        Gate::authorize('update', $tutor);

        $validated = $request->validated();

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $tutor->update($updateData);

        return back()->with('success', "Data profil tutor '{$tutor->name}' berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, User $tutor): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $tutor->tenant_id !== $tenant->id || $tutor->role !== 'tutor') {
            abort(404, 'Data tutor tidak ditemukan.');
        }

        Gate::authorize('update', $tutor);

        $newStatus = $tutor->status === 'active' ? 'inactive' : 'active';
        $tutor->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status tutor '{$tutor->name}' berhasil {$label}.");
    }

    public function assignClass(AssignTutorClassRequest $request, User $tutor): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $tutor->tenant_id !== $tenant->id || $tutor->role !== 'tutor') {
            abort(404, 'Data tutor tidak ditemukan.');
        }

        Gate::authorize('update', $tutor);

        $validated = $request->validated();
        $class = Classes::findOrFail($validated['class_id']);

        if (! $user->hasBranchAccess($class->branch_id)) {
            abort(403, 'Anda tidak memiliki akses ke cabang kelas ini.');
        }

        $existing = TutorAssignment::where('tenant_id', $tenant->id)
            ->where('tutor_id', $tutor->id)
            ->where('class_id', $class->id)
            ->first();

        if ($existing) {
            $existing->update([
                'branch_id' => $class->branch_id,
                'started_at' => $validated['started_at'] ?? $existing->started_at ?? now()->toDateString(),
                'ended_at' => $validated['ended_at'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);
        } else {
            TutorAssignment::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_id' => $class->branch_id,
                'tutor_id' => $tutor->id,
                'class_id' => $class->id,
                'started_at' => $validated['started_at'] ?? now()->toDateString(),
                'ended_at' => $validated['ended_at'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);
        }

        return back()->with('success', "Tutor berhasil ditugaskan pada kelas {$class->name}.");
    }

    public function toggleClassAssignment(Request $request, User $tutor, TutorAssignment $assignment): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $tutor->tenant_id !== $tenant->id || $tutor->role !== 'tutor' || $assignment->tutor_id !== $tutor->id) {
            abort(404, 'Penugasan tutor tidak ditemukan.');
        }

        if (! $user->hasBranchAccess($assignment->branch_id)) {
            abort(403, 'Di luar cakupan cabang akses Anda.');
        }

        Gate::authorize('update', $tutor);

        $newStatus = $assignment->status === 'active' ? 'inactive' : 'active';
        $assignment->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan kembali' : 'dinonaktifkan';

        return back()->with('success', "Penugasan kelas berhasil {$label}.");
    }
}
