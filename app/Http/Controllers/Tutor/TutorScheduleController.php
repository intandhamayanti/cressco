<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Schedule;
use App\Models\TeachingSession;
use App\Models\TutorAssignment;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TutorScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        // Assigned class IDs for this tutor
        $assignedClassIds = TutorAssignment::where('tenant_id', $tenant->id)
            ->where('tutor_id', $user->id)
            ->where('status', 'active')
            ->pluck('class_id')
            ->all();

        // Branches where this tutor has assigned classes, schedules, or sessions
        $tutorBranchIds = TutorAssignment::where('tenant_id', $tenant->id)
            ->where('tutor_id', $user->id)
            ->where('status', 'active')
            ->pluck('branch_id')
            ->merge(
                Schedule::where('tenant_id', $tenant->id)
                    ->where('scheduled_tutor_id', $user->id)
                    ->pluck('branch_id')
            )
            ->merge(
                TeachingSession::where('tenant_id', $tenant->id)
                    ->where(function ($q) use ($user) {
                        $q->where('scheduled_tutor_id', $user->id)
                            ->orWhere('actual_tutor_id', $user->id);
                    })
                    ->pluck('branch_id')
            )
            ->unique()
            ->filter()
            ->all();

        $tutorBranches = Branch::where('tenant_id', $tenant->id)
            ->whereIn('id', $tutorBranchIds)
            ->orderBy('name')
            ->get();

        $selectedDay = $request->query('day_of_week', 'all');
        $selectedBranch = $request->query('branch_id', 'all');
        $viewType = $request->query('view', 'week'); // 'week', 'day', 'month', 'sessions'

        $dateParam = $request->query('date');
        if ($dateParam) {
            try {
                $currentDate = Carbon::parse($dateParam);
            } catch (\Exception $e) {
                $currentDate = Carbon::today();
            }
        } elseif ($selectedDay !== 'all' && is_numeric($selectedDay)) {
            $currentDate = Carbon::today()->startOfWeek(Carbon::SUNDAY)->addDays((int) $selectedDay);
        } else {
            $currentDate = Carbon::today();
        }

        // Base Schedule Query (Strictly for this tutor)
        $schedulesQuery = Schedule::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('scheduled_tutor_id', $user->id)
            ->with(['class.branch', 'scheduledTutor'])
            ->orderBy('day_of_week')
            ->orderBy('start_time');

        if ($selectedDay !== 'all' && is_numeric($selectedDay)) {
            $schedulesQuery->where('day_of_week', (int) $selectedDay);
        }

        if ($selectedBranch !== 'all' && in_array($selectedBranch, $tutorBranchIds, true)) {
            $schedulesQuery->where('branch_id', $selectedBranch);
        }

        $schedules = $schedulesQuery->get();

        // Base Sessions Query (Teaching sessions for tutor - scheduled or replacement)
        $sessionsQuery = TeachingSession::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($user) {
                $q->where('scheduled_tutor_id', $user->id)
                    ->orWhere('actual_tutor_id', $user->id);
            })
            ->with(['class.branch', 'scheduledTutor', 'actualTutor'])
            ->withCount('studentAttendances')
            ->latest('session_date')
            ->orderBy('start_time', 'desc');

        if ($selectedBranch !== 'all' && in_array($selectedBranch, $tutorBranchIds, true)) {
            $sessionsQuery->where('branch_id', $selectedBranch);
        }

        $statusFilter = $request->query('status', 'all');
        if ($statusFilter !== 'all') {
            $sessionsQuery->where('status', $statusFilter);
        }

        $sessions = $sessionsQuery->paginate(15)->withQueryString();

        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        // Stats
        $totalSchedulesCount = Schedule::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('scheduled_tutor_id', $user->id)
            ->count();

        $today = Carbon::today();
        $todaySchedulesCount = Schedule::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('day_of_week', $today->dayOfWeek)
            ->where('scheduled_tutor_id', $user->id)
            ->count();

        $monthName = $currentDate->translatedFormat('F Y');
        $startOfCal = $currentDate->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $endOfCal = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $monthSessionsDates = TeachingSession::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($user) {
                $q->where('scheduled_tutor_id', $user->id)
                    ->orWhere('actual_tutor_id', $user->id);
            })
            ->whereBetween('session_date', [$currentDate->copy()->startOfMonth()->toDateString(), $currentDate->copy()->endOfMonth()->toDateString()])
            ->pluck('session_date')
            ->map(fn ($d) => Carbon::parse($d)->day)
            ->flip()
            ->all();

        $activeSchedulesDaysOfWeek = Schedule::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('scheduled_tutor_id', $user->id)
            ->pluck('day_of_week')
            ->unique()
            ->all();

        $calendarDays = [];
        $currPointer = $startOfCal->copy();
        while ($currPointer <= $endOfCal) {
            $isCurrMonth = $currPointer->month === $currentDate->month;
            $isTargetDate = $currPointer->isSameDay($currentDate);
            $dayNum = $currPointer->day;
            $hasSchedule = false;
            if ($isCurrMonth) {
                if (isset($monthSessionsDates[$dayNum]) || in_array($currPointer->dayOfWeek, $activeSchedulesDaysOfWeek, true)) {
                    $hasSchedule = true;
                }
            }

            $calendarDays[] = [
                'd' => $dayNum,
                'curr' => $isCurrMonth,
                'active' => $isTargetDate,
                'dots' => $hasSchedule ? ['terracotta'] : [],
                'url' => route('tutor.schedules.index', array_merge(request()->except(['date', 'day_of_week']), [
                    'date' => $currPointer->toDateString(),
                    'day_of_week' => $currPointer->dayOfWeek,
                ])),
            ];

            $currPointer->addDay();
        }

        // Time slots for Day and Week planners (08:00 AM to 08:00 PM)
        $timeSlots = [
            '08:00:00' => '08:00 AM',
            '09:00:00' => '09:00 AM',
            '10:00:00' => '10:00 AM',
            '11:00:00' => '11:00 AM',
            '12:00:00' => '12:00 PM',
            '13:00:00' => '01:00 PM',
            '14:00:00' => '02:00 PM',
            '15:00:00' => '03:00 PM',
            '16:00:00' => '04:00 PM',
            '17:00:00' => '05:00 PM',
            '18:00:00' => '06:00 PM',
            '19:00:00' => '07:00 PM',
            '20:00:00' => '08:00 PM',
        ];

        // 7-day week columns for Week Planner
        $weekColumns = [];
        $startOfWeek = $currentDate->copy()->startOfWeek(Carbon::SUNDAY);
        for ($i = 0; $i < 7; $i++) {
            $cDate = $startOfWeek->copy()->addDays($i);
            $weekColumns[] = [
                'date' => $cDate->toDateString(),
                'day' => $cDate->day,
                'short_name' => $cDate->translatedFormat('D'),
                'label' => $cDate->translatedFormat('D d'),
                'is_active' => $cDate->isSameDay($currentDate),
                'day_of_week' => $cDate->dayOfWeek,
            ];
        }

        // Selected Day Events / Schedules for Day View Planner
        $selectedDaySchedules = $schedules->filter(function ($s) use ($currentDate) {
            return $s->day_of_week === $currentDate->dayOfWeek;
        });

        // Top reminder item (next upcoming session today or this week)
        $nextUpcomingSession = TeachingSession::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($user) {
                $q->where('scheduled_tutor_id', $user->id)
                    ->orWhere('actual_tutor_id', $user->id);
            })
            ->whereDate('session_date', '>=', $today->toDateString())
            ->where('status', 'scheduled')
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->first();

        // Previous and Next month URLs for mini calendar
        $prevMonthUrl = route('tutor.schedules.index', array_merge(request()->except('date'), [
            'date' => $currentDate->copy()->subMonth()->startOfMonth()->toDateString(),
        ]));
        $nextMonthUrl = route('tutor.schedules.index', array_merge(request()->except('date'), [
            'date' => $currentDate->copy()->addMonth()->startOfMonth()->toDateString(),
        ]));

        return view('tutor.schedules.index', compact(
            'tenant',
            'user',
            'schedules',
            'sessions',
            'tutorBranches',
            'selectedDay',
            'selectedBranch',
            'viewType',
            'statusFilter',
            'days',
            'totalSchedulesCount',
            'todaySchedulesCount',
            'monthName',
            'calendarDays',
            'currentDate',
            'timeSlots',
            'weekColumns',
            'selectedDaySchedules',
            'nextUpcomingSession',
            'prevMonthUrl',
            'nextMonthUrl'
        ));
    }
}
