<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\TutorAssignment;
use App\Services\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TutorClassController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', Classes::class);

        // Assigned class IDs for this tutor
        $assignedClassIds = TutorAssignment::where('tenant_id', $tenant->id)
            ->where('tutor_id', $user->id)
            ->where('status', 'active')
            ->pluck('class_id')
            ->all();

        // Branches where this tutor has assigned classes
        $tutorBranchIds = TutorAssignment::where('tenant_id', $tenant->id)
            ->where('tutor_id', $user->id)
            ->pluck('branch_id')
            ->unique()
            ->filter()
            ->all();

        $tutorBranches = Branch::where('tenant_id', $tenant->id)
            ->whereIn('id', $tutorBranchIds)
            ->orderBy('name')
            ->get();

        $search = $request->query('search');
        $selectedBranch = $request->query('branch_id', 'all');

        $query = Classes::where('tenant_id', $tenant->id)
            ->whereIn('id', $assignedClassIds)
            ->with(['branch', 'schedules' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->where('status', 'active')->orderBy('day_of_week')->orderBy('start_time');
            }])
            ->withCount(['enrollments as active_students_count' => function ($q) {
                $q->where('status', 'active');
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('level', 'like', "%{$search}%");
            });
        }

        if ($selectedBranch !== 'all' && in_array($selectedBranch, $tutorBranchIds, true)) {
            $query->where('branch_id', $selectedBranch);
        }

        $classes = $query->orderBy('name')->paginate(12)->withQueryString();

        $totalAssignedClasses = count($assignedClassIds);
        $totalActiveStudents = Classes::where('tenant_id', $tenant->id)
            ->whereIn('id', $assignedClassIds)
            ->withCount(['enrollments as active_students_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->sum('active_students_count');

        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return view('tutor.classes.index', compact(
            'tenant',
            'user',
            'classes',
            'tutorBranches',
            'search',
            'selectedBranch',
            'totalAssignedClasses',
            'totalActiveStudents',
            'days'
        ));
    }

    public function show(Request $request, Classes $class): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $class->tenant_id !== $tenant->id) {
            abort(404, 'Kelas tidak ditemukan.');
        }

        // Enforce teaching scope check via Gate / Policy
        Gate::authorize('view', $class);

        $class->load([
            'branch',
            'enrollments' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with('student')->latest();
            },
            'schedules' => function ($q) use ($tenant, $user) {
                $q->where('tenant_id', $tenant->id)
                    ->where('scheduled_tutor_id', $user->id)
                    ->with('scheduledTutor')
                    ->orderBy('day_of_week')
                    ->orderBy('start_time');
            },
            'tutors' => function ($q) use ($tenant) {
                $q->where('users.tenant_id', $tenant->id);
            },
            'teachingSessions' => function ($q) use ($tenant, $user) {
                $q->where('tenant_id', $tenant->id)
                    ->where(function ($sq) use ($user) {
                        $sq->where('scheduled_tutor_id', $user->id)
                            ->orWhere('actual_tutor_id', $user->id);
                    })
                    ->with(['scheduledTutor', 'actualTutor'])
                    ->withCount('studentAttendances')
                    ->latest('session_date')
                    ->limit(15);
            },
        ]);

        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return view('tutor.classes.show', compact(
            'tenant',
            'user',
            'class',
            'days'
        ));
    }
}
