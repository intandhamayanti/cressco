<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\AssignTutorClassRequest;
use App\Http\Requests\Owner\AssignTutorHonorSchemeRequest;
use App\Http\Requests\Owner\StoreTutorRequest;
use App\Http\Requests\Owner\UpdateTutorRequest;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\HonorAssignment;
use App\Models\HonorScheme;
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

class OwnerTutorController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', User::class);

        $search = $request->query('search');
        $branchId = $request->query('branch_id', 'all');
        $status = $request->query('status', 'all');

        $query = User::where('tenant_id', $tenant->id)
            ->where('role', 'tutor')
            ->with([
                'tutorAssignments' => function ($q) use ($tenant) {
                    $q->where('tenant_id', $tenant->id)->with('class.branch');
                },
                'honorAssignments' => function ($q) use ($tenant) {
                    $q->where('tenant_id', $tenant->id)->where('assignment_type', 'tutor_override')->with('honorScheme');
                },
                'actualTeachingSessions' => function ($q) use ($tenant) {
                    $q->where('tenant_id', $tenant->id)->where('status', 'completed');
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
            $query->where(function ($q) use ($branchId) {
                $q->whereHas('tutorAssignments', fn ($sq) => $sq->where('branch_id', $branchId))
                    ->orWhereHas('actualTeachingSessions', fn ($sq) => $sq->where('branch_id', $branchId));
            });
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $tutors = $query->orderBy('name')->get();

        // Default tenant honor scheme
        $defaultSchemeAssignment = HonorAssignment::where('tenant_id', $tenant->id)
            ->where('assignment_type', 'default')
            ->with('honorScheme')
            ->first();
        $defaultScheme = $defaultSchemeAssignment?->honorScheme;

        // Metrics
        $totalTutorsCount = User::where('tenant_id', $tenant->id)->where('role', 'tutor')->count();
        $activeTutorsCount = User::where('tenant_id', $tenant->id)->where('role', 'tutor')->where('status', 'active')->count();
        $inactiveTutorsCount = $totalTutorsCount - $activeTutorsCount;
        $totalClassAssignmentsCount = TutorAssignment::where('tenant_id', $tenant->id)->where('status', 'active')->count();
        $totalCompletedSessionsCount = TeachingSession::where('tenant_id', $tenant->id)->where('status', 'completed')->count();

        $branches = Branch::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $honorSchemes = HonorScheme::where('tenant_id', $tenant->id)->where('status', 'active')->orderBy('name')->get();
        $classes = Classes::where('tenant_id', $tenant->id)->where('status', 'active')->with('branch')->orderBy('name')->get();

        return view('owner.tutors.index', compact(
            'tenant',
            'tutors',
            'defaultScheme',
            'branches',
            'honorSchemes',
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
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('create', User::class);

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

        // Optional Tutor Override Honor Scheme
        if (! empty($validated['honor_scheme_id'])) {
            HonorAssignment::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'tutor_id' => $tutor->id,
                'honor_scheme_id' => $validated['honor_scheme_id'],
                'assignment_type' => 'tutor_override',
                'effective_from' => now()->startOfMonth()->toDateString(),
            ]);
        }

        // Optional Class Assignment
        if (! empty($validated['class_id'])) {
            $class = Classes::find($validated['class_id']);
            if ($class && $class->tenant_id === $tenant->id) {
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

        return redirect()->route('owner.tutors.show', $tutor)
            ->with('success', "Tutor '{$tutor->name}' berhasil ditambahkan.");
    }

    public function show(Request $request, User $tutor): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $tutor->tenant_id !== $tenant->id || $tutor->role !== 'tutor') {
            abort(404, 'Data tutor tidak ditemukan.');
        }

        Gate::authorize('view', $tutor);

        $tutor->load([
            'tutorAssignments' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with('class.branch')->latest();
            },
            'scheduledSchedules' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with('class.branch')->orderBy('day_of_week')->orderBy('start_time');
            },
            'actualTeachingSessions' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with(['class.branch', 'scheduledTutor'])->latest('session_date')->limit(30);
            },
            'honorAssignments' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with('honorScheme');
            },
            'tutorHonorCalculations' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with(['honorScheme', 'branch'])->latest('period_start');
            },
        ]);

        $defaultSchemeAssignment = HonorAssignment::where('tenant_id', $tenant->id)
            ->where('assignment_type', 'default')
            ->with('honorScheme')
            ->first();
        $defaultScheme = $defaultSchemeAssignment?->honorScheme;

        $tutorOverrideAssignment = $tutor->honorAssignments->where('assignment_type', 'tutor_override')->first();
        $activeScheme = $tutorOverrideAssignment?->honorScheme ?? $defaultScheme;

        $honorSchemes = HonorScheme::where('tenant_id', $tenant->id)->where('status', 'active')->orderBy('name')->get();
        $classes = Classes::where('tenant_id', $tenant->id)->where('status', 'active')->with('branch')->orderBy('name')->get();

        return view('owner.tutors.show', compact(
            'tenant',
            'tutor',
            'defaultScheme',
            'tutorOverrideAssignment',
            'activeScheme',
            'honorSchemes',
            'classes'
        ));
    }

    public function update(UpdateTutorRequest $request, User $tutor): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

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

        // Update or remove Honor Scheme Override
        if (! empty($validated['honor_scheme_id'])) {
            $override = HonorAssignment::where('tenant_id', $tenant->id)
                ->where('tutor_id', $tutor->id)
                ->where('assignment_type', 'tutor_override')
                ->first();

            if ($override) {
                $override->update([
                    'honor_scheme_id' => $validated['honor_scheme_id'],
                ]);
            } else {
                HonorAssignment::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant->id,
                    'tutor_id' => $tutor->id,
                    'honor_scheme_id' => $validated['honor_scheme_id'],
                    'assignment_type' => 'tutor_override',
                    'effective_from' => now()->startOfMonth()->toDateString(),
                ]);
            }
        } else {
            // Remove override if cleared
            HonorAssignment::where('tenant_id', $tenant->id)
                ->where('tutor_id', $tutor->id)
                ->where('assignment_type', 'tutor_override')
                ->delete();
        }

        return back()->with('success', "Data tutor '{$tutor->name}' berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, User $tutor): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

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
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $tutor->tenant_id !== $tenant->id || $tutor->role !== 'tutor') {
            abort(404, 'Data tutor tidak ditemukan.');
        }

        Gate::authorize('update', $tutor);

        $validated = $request->validated();
        $class = Classes::findOrFail($validated['class_id']);

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

        return back()->with('success', "Tutor {$tutor->name} berhasil ditugaskan ke kelas '{$class->name}'.");
    }

    public function toggleClassAssignment(Request $request, User $tutor, TutorAssignment $assignment): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $tutor->tenant_id !== $tenant->id || $assignment->tenant_id !== $tenant->id || $assignment->tutor_id !== $tutor->id) {
            abort(404, 'Penugasan kelas tidak ditemukan.');
        }

        Gate::authorize('update', $tutor);

        $newStatus = $assignment->status === 'active' ? 'inactive' : 'active';
        $assignment->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan kembali' : 'dinonaktifkan';

        return back()->with('success', "Penugasan kelas {$assignment->class?->name} berhasil {$label}.");
    }

    public function assignHonorScheme(AssignTutorHonorSchemeRequest $request, User $tutor): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $tutor->tenant_id !== $tenant->id || $tutor->role !== 'tutor') {
            abort(404, 'Data tutor tidak ditemukan.');
        }

        Gate::authorize('update', $tutor);

        $validated = $request->validated();

        if (empty($validated['honor_scheme_id'])) {
            HonorAssignment::where('tenant_id', $tenant->id)
                ->where('tutor_id', $tutor->id)
                ->where('assignment_type', 'tutor_override')
                ->delete();

            return back()->with('success', "Skema honor khusus untuk {$tutor->name} telah dihapus. Tutor kini menggunakan skema default tenant.");
        }

        $override = HonorAssignment::where('tenant_id', $tenant->id)
            ->where('tutor_id', $tutor->id)
            ->where('assignment_type', 'tutor_override')
            ->first();

        if ($override) {
            $override->update([
                'honor_scheme_id' => $validated['honor_scheme_id'],
                'effective_from' => $validated['effective_from'],
                'effective_until' => $validated['effective_until'] ?? null,
            ]);
        } else {
            HonorAssignment::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'tutor_id' => $tutor->id,
                'honor_scheme_id' => $validated['honor_scheme_id'],
                'assignment_type' => 'tutor_override',
                'effective_from' => $validated['effective_from'],
                'effective_until' => $validated['effective_until'] ?? null,
            ]);
        }

        $schemeName = HonorScheme::find($validated['honor_scheme_id'])?->name ?? 'Skema Honor';

        return back()->with('success', "Skema honor khusus '{$schemeName}' berhasil diterapkan untuk {$tutor->name}.");
    }
}
