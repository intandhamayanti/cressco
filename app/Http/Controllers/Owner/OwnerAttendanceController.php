<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OwnerAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', StudentAttendance::class);

        $branches = Branch::where('tenant_id', $tenant->id)->orderBy('name')->get();
        $classes = Classes::where('tenant_id', $tenant->id)->where('status', 'active')->orderBy('name')->get();

        $search = $request->query('search');
        $branchId = $request->query('branch_id', 'all');
        $classId = $request->query('class_id', 'all');
        $status = $request->query('status', 'all');
        $dateFrom = $request->query('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', Carbon::now()->endOfMonth()->toDateString());

        $query = StudentAttendance::where('tenant_id', $tenant->id)
            ->with([
                'student',
                'branch',
                'teachingSession.classModel',
                'teachingSession.scheduledTutor',
                'teachingSession.actualTutor',
                'recordedBy',
            ]);

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        if ($classId && $classId !== 'all') {
            $query->whereHas('teachingSession', fn ($q) => $q->where('class_id', $classId));
        }

        if ($status && $status !== 'all') {
            if (in_array($status, ['hadir', 'present'])) {
                $query->whereIn('status', ['hadir', 'present']);
            } elseif (in_array($status, ['izin', 'excused'])) {
                $query->whereIn('status', ['izin', 'excused']);
            } elseif ($status === 'sakit') {
                $query->where('status', 'sakit');
            } elseif (in_array($status, ['alpa', 'absent'])) {
                $query->whereIn('status', ['alpa', 'absent']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($dateFrom) {
            $query->whereDate('recorded_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('recorded_at', '<=', $dateTo);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $attendances = $query->latest('recorded_at')->paginate(20)->withQueryString();

        // Metrics for tenant oversight
        $metricQuery = StudentAttendance::where('tenant_id', $tenant->id);
        if ($branchId && $branchId !== 'all') {
            $metricQuery->where('branch_id', $branchId);
        }
        if ($dateFrom) {
            $metricQuery->whereDate('recorded_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $metricQuery->whereDate('recorded_at', '<=', $dateTo);
        }

        $totalAttendanceCount = (clone $metricQuery)->count();
        $presentCount = (clone $metricQuery)->whereIn('status', ['hadir', 'present'])->count();
        $permissionCount = (clone $metricQuery)->whereIn('status', ['izin', 'excused'])->count();
        $sickCount = (clone $metricQuery)->where('status', 'sakit')->count();
        $alpaCount = (clone $metricQuery)->whereIn('status', ['alpa', 'absent'])->count();

        $attendanceRate = $totalAttendanceCount > 0 ? round(($presentCount / $totalAttendanceCount) * 100) : 100;

        // Recent teaching sessions for oversight
        $recentSessions = TeachingSession::where('tenant_id', $tenant->id)
            ->when($branchId && $branchId !== 'all', fn ($q) => $q->where('branch_id', $branchId))
            ->with(['classModel', 'scheduledTutor', 'actualTutor', 'branch'])
            ->withCount('studentAttendances')
            ->latest('session_date')
            ->limit(6)
            ->get();

        return view('owner.attendances.index', compact(
            'tenant',
            'attendances',
            'branches',
            'classes',
            'recentSessions',
            'search',
            'branchId',
            'classId',
            'status',
            'dateFrom',
            'dateTo',
            'totalAttendanceCount',
            'presentCount',
            'permissionCount',
            'sickCount',
            'alpaCount',
            'attendanceRate'
        ));
    }

    public function showSession(Request $request, TeachingSession $session): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $session->tenant_id !== $tenant->id) {
            abort(404, 'Sesi pengajaran tidak ditemukan.');
        }

        Gate::authorize('view', $session);

        $session->load([
            'classModel',
            'scheduledTutor',
            'actualTutor',
            'branch',
            'studentAttendances.student',
            'tutorAttendance',
            'tutorReplacements.replacementTutor',
            'tutorReplacements.scheduledTutor',
        ]);

        return view('owner.attendances.session', compact('tenant', 'session'));
    }
}
