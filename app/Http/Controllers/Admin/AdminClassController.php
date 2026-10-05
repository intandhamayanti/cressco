<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignTutorRequest;
use App\Http\Requests\Admin\StoreClassRequest;
use App\Http\Requests\Admin\StoreScheduleRequest;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateClassRequest;
use App\Http\Requests\Admin\UpdateScheduleRequest;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TutorAssignment;
use App\Models\User;
use App\Services\TeachingSessionGenerationService;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminClassController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', Classes::class);

        $accessibleBranches = $user->accessibleBranches();
        $accessibleBranchIds = $user->accessibleBranchIds()->all();

        $search = $request->query('search');
        $branchId = $request->query('branch_id', 'all');
        $status = $request->query('status', 'all');

        $query = Classes::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->with([
                'branch',
                'tutorAssignments' => function ($q) use ($tenant) {
                    $q->where('tenant_id', $tenant->id)->with('tutor');
                },
                'schedules' => function ($q) use ($tenant) {
                    $q->where('tenant_id', $tenant->id)->with('scheduledTutor');
                },
                'enrollments' => function ($q) use ($tenant) {
                    $q->where('tenant_id', $tenant->id);
                },
            ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('level', 'like', "%{$search}%");
            });
        }

        if ($branchId && $branchId !== 'all') {
            if (in_array($branchId, $accessibleBranchIds, true)) {
                $query->where('branch_id', $branchId);
            }
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $classes = $query->orderBy('name')->get();

        // Metrics scoped to Admin's accessible branches
        $totalClassesCount = Classes::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->count();

        $activeClassesCount = Classes::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'active')
            ->count();

        $inactiveClassesCount = $totalClassesCount - $activeClassesCount;

        $totalEnrollmentsCount = Enrollment::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'active')
            ->count();

        $totalCapacityCount = (int) Classes::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'active')
            ->sum('capacity');

        $tutors = User::where('tenant_id', $tenant->id)
            ->whereIn('role', ['tutor', 'admin', 'owner'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.classes.index', compact(
            'tenant',
            'classes',
            'accessibleBranches',
            'tutors',
            'totalClassesCount',
            'activeClassesCount',
            'inactiveClassesCount',
            'totalEnrollmentsCount',
            'totalCapacityCount',
            'search',
            'branchId',
            'status'
        ));
    }

    public function store(StoreClassRequest $request): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        $validated = $request->validated();

        Gate::authorize('create', [Classes::class, $validated['branch_id']]);

        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $validated['branch_id'],
            'name' => $validated['name'],
            'subject' => $validated['subject'] ?? null,
            'level' => $validated['level'] ?? null,
            'capacity' => $validated['capacity'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        // Optional initial tutor assignment
        if (! empty($validated['tutor_id'])) {
            TutorAssignment::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_id' => $class->branch_id,
                'tutor_id' => $validated['tutor_id'],
                'class_id' => $class->id,
                'started_at' => now()->toDateString(),
                'status' => 'active',
            ]);
        }

        return redirect()->route('admin.classes.show', $class)
            ->with('success', "Kelas '{$class->name}' berhasil dibuat.");
    }

    public function show(Request $request, Classes $class): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id)) {
            abort(404, 'Kelas tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('view', $class);

        $class->load([
            'branch',
            'tutorAssignments' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with('tutor')->latest();
            },
            'schedules' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with('scheduledTutor')->orderBy('day_of_week')->orderBy('start_time');
            },
            'enrollments' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with('student')->latest();
            },
            'teachingSessions' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)
                    ->with(['scheduledTutor', 'actualTutor', 'studentAttendances', 'tutorReplacements.replacementTutor', 'tutorReplacements.changedBy'])
                    ->orderByDesc('session_date')
                    ->orderBy('start_time');
            },
        ]);

        $accessibleBranches = $user->accessibleBranches();
        $tutors = User::where('tenant_id', $tenant->id)
            ->whereIn('role', ['tutor', 'admin', 'owner'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Active students in class branch who are not actively enrolled in this class
        $activeEnrolledStudentIds = $class->enrollments->where('status', 'active')->pluck('student_id')->all();
        $availableStudents = Student::where('tenant_id', $tenant->id)
            ->where('branch_id', $class->branch_id)
            ->where('status', 'active')
            ->whereNotIn('id', $activeEnrolledStudentIds)
            ->orderBy('name')
            ->get();

        return view('admin.classes.show', compact('tenant', 'class', 'accessibleBranches', 'tutors', 'availableStudents'));
    }

    public function update(UpdateClassRequest $request, Classes $class): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id)) {
            abort(404, 'Kelas tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('update', $class);

        $validated = $request->validated();

        $class->update([
            'branch_id' => $validated['branch_id'],
            'name' => $validated['name'],
            'subject' => $validated['subject'] ?? null,
            'level' => $validated['level'] ?? null,
            'capacity' => $validated['capacity'] ?? null,
            'status' => $validated['status'],
        ]);

        return back()->with('success', "Data kelas '{$class->name}' berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, Classes $class): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id)) {
            abort(404, 'Kelas tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('update', $class);

        $newStatus = $class->status === 'active' ? 'inactive' : 'active';
        $class->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status kelas '{$class->name}' berhasil {$label}.");
    }

    public function assignTutor(AssignTutorRequest $request, Classes $class): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id)) {
            abort(404, 'Kelas tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('update', $class);

        $validated = $request->validated();

        $existing = TutorAssignment::where('tenant_id', $tenant->id)
            ->where('class_id', $class->id)
            ->where('tutor_id', $validated['tutor_id'])
            ->first();

        if ($existing) {
            $existing->update([
                'branch_id' => $class->branch_id,
                'started_at' => $validated['started_at'] ?? $existing->started_at ?? now()->toDateString(),
                'ended_at' => $validated['ended_at'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);
            $tutorName = $existing->tutor?->name ?? 'Tutor';
        } else {
            $assignment = TutorAssignment::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_id' => $class->branch_id,
                'tutor_id' => $validated['tutor_id'],
                'class_id' => $class->id,
                'started_at' => $validated['started_at'] ?? now()->toDateString(),
                'ended_at' => $validated['ended_at'] ?? null,
                'status' => $validated['status'] ?? 'active',
            ]);
            $tutorName = $assignment->tutor?->name ?? 'Tutor';
        }

        return back()->with('success', "Tutor {$tutorName} berhasil ditugaskan pada kelas ini.");
    }

    public function toggleTutorAssignment(Request $request, Classes $class, TutorAssignment $assignment): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id) || $assignment->tenant_id !== $tenant->id || $assignment->class_id !== $class->id) {
            abort(404, 'Penugasan tutor tidak ditemukan.');
        }

        Gate::authorize('update', $class);

        $newStatus = $assignment->status === 'active' ? 'inactive' : 'active';
        $assignment->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan kembali' : 'dinonaktifkan';

        return back()->with('success', "Penugasan tutor {$assignment->tutor?->name} berhasil {$label}.");
    }

    public function storeSchedule(StoreScheduleRequest $request, Classes $class): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id)) {
            abort(404, 'Kelas tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('update', $class);

        $validated = $request->validated();

        $schedule = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $class->branch_id,
            'class_id' => $class->id,
            'scheduled_tutor_id' => $validated['scheduled_tutor_id'],
            'day_of_week' => (int) $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'room' => $validated['room'] ?? null,
            'starts_on' => $validated['starts_on'],
            'ends_on' => $validated['ends_on'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        // Auto generate initial teaching sessions for this recurring schedule (next 30 days)
        if ($schedule->status === 'active') {
            $startsOn = Carbon::parse($schedule->starts_on);
            $endsOn = $schedule->ends_on ? Carbon::parse($schedule->ends_on) : $startsOn->copy()->addDays(30);
            app(TeachingSessionGenerationService::class)->generateForSchedule($schedule, $startsOn, $endsOn);
        }

        return back()->with('success', 'Jadwal rutin baru berhasil ditambahkan.');
    }

    public function updateSchedule(UpdateScheduleRequest $request, Classes $class, Schedule $schedule): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id) || $schedule->tenant_id !== $tenant->id || $schedule->class_id !== $class->id) {
            abort(404, 'Jadwal tidak ditemukan.');
        }

        Gate::authorize('update', $class);

        $validated = $request->validated();

        $schedule->update([
            'scheduled_tutor_id' => $validated['scheduled_tutor_id'],
            'day_of_week' => (int) $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'room' => $validated['room'] ?? null,
            'starts_on' => $validated['starts_on'],
            'ends_on' => $validated['ends_on'] ?? null,
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Jadwal rutin berhasil diperbarui.');
    }

    public function toggleScheduleStatus(Request $request, Classes $class, Schedule $schedule): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id) || $schedule->tenant_id !== $tenant->id || $schedule->class_id !== $class->id) {
            abort(404, 'Jadwal tidak ditemukan.');
        }

        Gate::authorize('update', $class);

        $newStatus = $schedule->status === 'active' ? 'inactive' : 'active';
        $schedule->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan kembali' : 'dinonaktifkan';

        return back()->with('success', "Jadwal berhasil {$label}.");
    }

    public function destroySchedule(Request $request, Classes $class, Schedule $schedule): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id) || $schedule->tenant_id !== $tenant->id || $schedule->class_id !== $class->id) {
            abort(404, 'Jadwal tidak ditemukan.');
        }

        Gate::authorize('update', $class);

        $schedule->delete();

        return back()->with('success', 'Jadwal rutin berhasil dihapus.');
    }

    public function enrollStudents(Request $request, Classes $class): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id)) {
            abort(404, 'Kelas tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('update', $class);

        $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'uuid'],
            'started_at' => ['nullable', 'date'],
        ]);

        $studentIds = array_unique($request->input('student_ids'));
        $startedAt = $request->input('started_at', now()->toDateString());
        $count = 0;

        foreach ($studentIds as $studentId) {
            $student = Student::where('tenant_id', $tenant->id)
                ->where('branch_id', $class->branch_id)
                ->find($studentId);

            if ($student) {
                $enrollment = Enrollment::where('tenant_id', $tenant->id)
                    ->where('class_id', $class->id)
                    ->where('student_id', $student->id)
                    ->first();

                if ($enrollment) {
                    $enrollment->update([
                        'status' => 'active',
                        'started_at' => $startedAt,
                    ]);
                } else {
                    Enrollment::create([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenant->id,
                        'branch_id' => $class->branch_id,
                        'student_id' => $student->id,
                        'class_id' => $class->id,
                        'started_at' => $startedAt,
                        'status' => 'active',
                    ]);
                }
                $count++;
            }
        }

        return back()->with('success', "Berhasil menambahkan {$count} siswa ke kelas '{$class->name}'.");
    }

    public function storeStudent(StoreStudentRequest $request, Classes $class): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id)) {
            abort(404, 'Kelas tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('create', [Student::class, $class->branch_id]);

        $validated = $request->validated();

        $newStudent = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $class->branch_id,
            'name' => $validated['name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'parent_name' => $validated['parent_name'] ?? null,
            'parent_phone' => $validated['parent_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'joined_at' => $validated['joined_at'] ?? now()->toDateString(),
            'status' => $validated['status'] ?? 'active',
        ]);

        // Auto-enroll to this class
        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $class->branch_id,
            'student_id' => $newStudent->id,
            'class_id' => $class->id,
            'started_at' => $newStudent->joined_at ?? now()->toDateString(),
            'status' => 'active',
        ]);

        return back()->with('success', "Siswa '{$newStudent->name}' berhasil dibuat dan didaftarkan ke kelas '{$class->name}'.");
    }

    public function toggleEnrollment(Request $request, Classes $class, Enrollment $enrollment): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id) || $enrollment->tenant_id !== $tenant->id || $enrollment->class_id !== $class->id) {
            abort(404, 'Data enrollment tidak ditemukan.');
        }

        Gate::authorize('update', $class);

        $newStatus = $enrollment->status === 'active' ? 'withdrawn' : 'active';
        $enrollment->update([
            'status' => $newStatus,
            'ended_at' => $newStatus === 'withdrawn' ? now()->toDateString() : null,
        ]);

        $label = $newStatus === 'active' ? 'diaktifkan kembali' : 'dinonaktifkan';

        return back()->with('success', "Status enrollment siswa '{$enrollment->student?->name}' berhasil {$label}.");
    }

    public function destroyEnrollment(Request $request, Classes $class, Enrollment $enrollment): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || ! $user->hasBranchAccess($class->branch_id) || $enrollment->tenant_id !== $tenant->id || $enrollment->class_id !== $class->id) {
            abort(404, 'Data enrollment tidak ditemukan.');
        }

        Gate::authorize('update', $class);

        $studentName = $enrollment->student?->name ?? 'Siswa';
        $enrollment->delete();

        return back()->with('success', "Siswa '{$studentName}' berhasil dikeluarkan dari kelas.");
    }
}
