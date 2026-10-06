<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use App\Models\Schedule;
use App\Models\TeachingSession;
use App\Models\TutorAssignment;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TutorDashboardController extends Controller
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

        // 1. Assigned Classes (Active)
        $assignedClasses = Classes::where('tenant_id', $tenant->id)
            ->whereIn('id', $assignedClassIds)
            ->where('status', 'active')
            ->with(['branch', 'schedules' => function ($q) use ($tenant, $user) {
                $q->where('tenant_id', $tenant->id)
                    ->where('scheduled_tutor_id', $user->id)
                    ->where('status', 'active');
            }])
            ->withCount(['enrollments as active_students_count' => function ($q) {
                $q->where('status', 'active');
            }])
            ->orderBy('name')
            ->get();

        // 2. Today's Schedule & Sessions
        $today = Carbon::today();
        $todayDayOfWeek = $today->dayOfWeek; // 0 (Sun) - 6 (Sat)

        // Today's recurring schedules for this tutor
        $todaySchedules = Schedule::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('day_of_week', $todayDayOfWeek)
            ->where('scheduled_tutor_id', $user->id)
            ->with(['class.branch', 'scheduledTutor'])
            ->orderBy('start_time')
            ->get();

        // Today's specific teaching sessions (if created)
        $todaySessions = TeachingSession::where('tenant_id', $tenant->id)
            ->whereDate('session_date', $today->toDateString())
            ->where(function ($q) use ($user) {
                $q->where('scheduled_tutor_id', $user->id)
                    ->orWhere('actual_tutor_id', $user->id);
            })
            ->with(['class.branch', 'scheduledTutor', 'actualTutor'])
            ->orderBy('start_time')
            ->get();

        // 3. Upcoming Teaching Sessions (Next 7 days)
        $upcomingSessions = TeachingSession::where('tenant_id', $tenant->id)
            ->whereDate('session_date', '>=', $today->toDateString())
            ->where(function ($q) use ($user) {
                $q->where('scheduled_tutor_id', $user->id)
                    ->orWhere('actual_tutor_id', $user->id);
            })
            ->with(['class.branch', 'scheduledTutor', 'actualTutor'])
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();

        // 4. Metrics & Statistics
        $totalClassesCount = $assignedClasses->count();
        $totalStudentsCount = $assignedClasses->sum('active_students_count');

        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $completedSessionsCount = TeachingSession::where('tenant_id', $tenant->id)
            ->where('status', 'completed')
            ->where(function ($q) use ($user) {
                $q->where('actual_tutor_id', $user->id)
                    ->orWhere(function ($sq) use ($user) {
                        $sq->whereNull('actual_tutor_id')
                            ->where('scheduled_tutor_id', $user->id);
                    });
            })
            ->whereBetween('session_date', [$startOfMonth, $endOfMonth])
            ->count();

        $weeklySchedulesCount = Schedule::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('scheduled_tutor_id', $user->id)
            ->count();

        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        // 5. Calendar and Date Strip structures for reusable components
        $dateParam = $request->query('date') ?? $request->query('month');
        if ($dateParam) {
            try {
                $calendarDate = Carbon::parse($dateParam);
            } catch (\Exception $e) {
                $calendarDate = Carbon::today();
            }
        } else {
            $calendarDate = Carbon::today();
        }

        $monthName = $calendarDate->translatedFormat('F Y');
        $startOfCal = $calendarDate->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $endOfCal = $calendarDate->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $monthSessionsDates = TeachingSession::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($user) {
                $q->where('scheduled_tutor_id', $user->id)
                    ->orWhere('actual_tutor_id', $user->id);
            })
            ->whereBetween('session_date', [$calendarDate->copy()->startOfMonth()->toDateString(), $calendarDate->copy()->endOfMonth()->toDateString()])
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
            $isCurrMonth = $currPointer->month === $calendarDate->month;
            $isToday = $currPointer->isSameDay($today);
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
                'active' => $isToday,
                'dots' => $hasSchedule ? ['terracotta'] : [],
                'url' => route('tutor.schedules.index', [
                    'date' => $currPointer->toDateString(),
                    'day_of_week' => $currPointer->dayOfWeek,
                ]),
            ];

            $currPointer->addDay();
        }

        $prevMonthUrl = route('tutor.dashboard', [
            'date' => $calendarDate->copy()->subMonth()->startOfMonth()->toDateString(),
        ]);
        $nextMonthUrl = route('tutor.dashboard', [
            'date' => $calendarDate->copy()->addMonth()->startOfMonth()->toDateString(),
        ]);

        // 7-day strip around today
        $weekDates = [];
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        for ($i = 0; $i < 7; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $hasSchedule = in_array($dayDate->dayOfWeek, $activeSchedulesDaysOfWeek, true);
            $weekDates[] = [
                'day' => $dayDate->day,
                'day_name' => $days[$dayDate->dayOfWeek] ?? '',
                'active' => $dayDate->isToday(),
                'dots' => $hasSchedule ? ['terracotta'] : [],
            ];
        }

        return view('tutor.dashboard', compact(
            'tenant',
            'user',
            'assignedClasses',
            'todaySchedules',
            'todaySessions',
            'upcomingSessions',
            'totalClassesCount',
            'totalStudentsCount',
            'completedSessionsCount',
            'weeklySchedulesCount',
            'days',
            'today',
            'monthName',
            'calendarDays',
            'weekDates',
            'prevMonthUrl',
            'nextMonthUrl'
        ));
    }
}
