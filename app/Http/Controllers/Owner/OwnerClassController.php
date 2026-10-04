<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\AssignTutorRequest;
use App\Http\Requests\Owner\StoreClassRequest;
use App\Http\Requests\Owner\StoreScheduleRequest;
use App\Http\Requests\Owner\UpdateClassRequest;
use App\Http\Requests\Owner\UpdateScheduleRequest;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Schedule;
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

class OwnerClassController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', Classes::class);

        $search = $request->query('search');
        $branchId = $request->query('branch_id', 'all');
        $status = $request->query('status', 'all');

        $query = Classes::where('tenant_id', $tenant->id)
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
            $query->where('branch_id', $branchId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $classes = $query->orderBy('name')->get();

        // Summary metrics
        $totalClassesCount = Classes::where('tenant_id', $tenant->id)->count();
        $activeClassesCount = Classes::where('tenant_id', $tenant->id)->where('status', 'active')->count();
        $inactiveClassesCount = $totalClassesCount - $activeClassesCount;
        $totalEnrollmentsCount = Enrollment::where('tenant_id', $tenant->id)->where('status', 'active')->count();
        $totalCapacityCount = Classes::where('tenant_id', $tenant->id)->where('status', 'active')->sum('capacity');

        $branches = Branch::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $tutors = User::where('tenant_id', $tenant->id)
            ->whereIn('role', ['tutor', 'admin', 'owner'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('owner.classes.index', compact(
            'tenant',
            'classes',
            'branches',
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
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('create', Classes::class);

        $validated = $request->validated();

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

        return redirect()->route('owner.classes.show', $class)
            ->with('success', "Kelas '{$class->name}' berhasil dibuat.");
    }

    public function show(Request $request, Classes $class): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id) {
            abort(404, 'Kelas tidak ditemukan.');
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
        ]);

        $branches = Branch::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $tutors = User::where('tenant_id', $tenant->id)
            ->whereIn('role', ['tutor', 'admin', 'owner'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('owner.classes.show', compact('tenant', 'class', 'branches', 'tutors'));
    }

    public function update(UpdateClassRequest $request, Classes $class): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id) {
            abort(404, 'Kelas tidak ditemukan.');
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
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id) {
            abort(404, 'Kelas tidak ditemukan.');
        }

        Gate::authorize('update', $class);

        $newStatus = $class->status === 'active' ? 'inactive' : 'active';
        $class->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status kelas '{$class->name}' berhasil {$label}.");
    }

    public function assignTutor(AssignTutorRequest $request, Classes $class): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id) {
            abort(404, 'Kelas tidak ditemukan.');
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
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || $assignment->tenant_id !== $tenant->id || $assignment->class_id !== $class->id) {
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
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id) {
            abort(404, 'Kelas tidak ditemukan.');
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

        // Auto ensure tutor assignment exists for this tutor
        TutorAssignment::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'class_id' => $class->id,
                'tutor_id' => $validated['scheduled_tutor_id'],
            ],
            [
                'id' => (string) Str::uuid(),
                'branch_id' => $class->branch_id,
                'started_at' => $validated['starts_on'],
                'status' => 'active',
            ]
        );

        // Auto generate initial teaching sessions for this recurring schedule (next 30 days)
        if ($schedule->status === 'active') {
            $startsOn = Carbon::parse($schedule->starts_on);
            $endsOn = $schedule->ends_on ? Carbon::parse($schedule->ends_on) : $startsOn->copy()->addDays(30);
            app(TeachingSessionGenerationService::class)->generateForSchedule($schedule, $startsOn, $endsOn);
        }

        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $dayName = $days[$schedule->day_of_week] ?? 'Hari';

        return back()->with('success', "Jadwal rutin setiap {$dayName} ({$schedule->start_time} - {$schedule->end_time}) berhasil ditambahkan.");
    }

    public function updateSchedule(UpdateScheduleRequest $request, Classes $class, Schedule $schedule): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || $schedule->tenant_id !== $tenant->id || $schedule->class_id !== $class->id) {
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
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || $schedule->tenant_id !== $tenant->id || $schedule->class_id !== $class->id) {
            abort(404, 'Jadwal tidak ditemukan.');
        }

        Gate::authorize('update', $class);

        $newStatus = $schedule->status === 'active' ? 'inactive' : 'active';
        $schedule->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status jadwal rutin berhasil {$label}.");
    }

    public function destroySchedule(Request $request, Classes $class, Schedule $schedule): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id || $schedule->tenant_id !== $tenant->id || $schedule->class_id !== $class->id) {
            abort(404, 'Jadwal tidak ditemukan.');
        }

        Gate::authorize('delete', $schedule);

        $schedule->delete();

        return back()->with('success', 'Jadwal rutin berhasil dihapus.');
    }
}
