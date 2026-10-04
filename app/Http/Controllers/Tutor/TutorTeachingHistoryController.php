<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\TeachingSession;
use App\Models\TutorAssignment;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TutorTeachingHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', TeachingSession::class);

        $assignedClassIds = TutorAssignment::where('tenant_id', $tenant->id)
            ->where('tutor_id', $user->id)
            ->pluck('class_id')
            ->all();

        $tutorClasses = Classes::where('tenant_id', $tenant->id)
            ->whereIn('id', $assignedClassIds)
            ->orderBy('name')
            ->get();

        $tutorBranches = Branch::where('tenant_id', $tenant->id)
            ->whereIn('id', $tutorClasses->pluck('branch_id')->unique()->filter())
            ->orderBy('name')
            ->get();

        $search = $request->query('search');
        $selectedBranch = $request->query('branch_id', 'all');
        $selectedClass = $request->query('class_id', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = TeachingSession::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($user) {
                $q->where('actual_tutor_id', $user->id)
                    ->orWhere('scheduled_tutor_id', $user->id);
            })
            ->where('status', 'completed')
            ->with(['classModel.branch', 'branch', 'scheduledTutor', 'actualTutor'])
            ->withCount('studentAttendances');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('material', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('room', 'like', "%{$search}%")
                    ->orWhereHas('classModel', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($selectedBranch !== 'all') {
            $query->where('branch_id', $selectedBranch);
        }

        if ($selectedClass !== 'all') {
            $query->where('class_id', $selectedClass);
        }

        if ($dateFrom) {
            $query->whereDate('session_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('session_date', '<=', $dateTo);
        }

        $sessions = $query->orderByDesc('session_date')->orderByDesc('start_time')->paginate(15)->withQueryString();

        // Calculate summary metrics
        $allCompletedSessions = TeachingSession::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($user) {
                $q->where('actual_tutor_id', $user->id)
                    ->orWhere('scheduled_tutor_id', $user->id);
            })
            ->where('status', 'completed')
            ->withCount('studentAttendances')
            ->get();

        $totalCompletedSessions = $allCompletedSessions->count();
        $totalStudentsTaught = $allCompletedSessions->sum('student_attendances_count');
        $distinctClassesCount = $allCompletedSessions->pluck('class_id')->unique()->count();

        // Calculate total teaching hours in hours & minutes
        $totalMinutes = 0;
        foreach ($allCompletedSessions as $sess) {
            if ($sess->start_time && $sess->end_time) {
                $start = Carbon::parse('2000-01-01 '.$sess->start_time);
                $end = Carbon::parse('2000-01-01 '.$sess->end_time);
                $totalMinutes += abs($end->diffInMinutes($start));
            }
        }
        $totalHoursFormatted = round($totalMinutes / 60, 1);

        return view('tutor.history.index', compact(
            'tenant',
            'user',
            'sessions',
            'tutorClasses',
            'tutorBranches',
            'search',
            'selectedBranch',
            'selectedClass',
            'dateFrom',
            'dateTo',
            'totalCompletedSessions',
            'totalStudentsTaught',
            'distinctClassesCount',
            'totalHoursFormatted'
        ));
    }
}
