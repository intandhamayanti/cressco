<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplaceTutorRequest;
use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\Classes;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Services\TenantContext;
use App\Services\TutorReplacementService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AdminAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', StudentAttendance::class);

        $accessibleBranches = $user->accessibleBranches();
        $accessibleBranchIds = $user->accessibleBranchIds()->all();

        $search = $request->query('search');
        $branchId = $request->query('branch_id', 'all');
        $classId = $request->query('class_id', 'all');
        $status = $request->query('status', 'all');
        $dateFrom = $request->query('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', Carbon::now()->endOfMonth()->toDateString());

        $effectiveBranchIds = ($branchId && $branchId !== 'all' && in_array($branchId, $accessibleBranchIds, true))
            ? [$branchId]
            : $accessibleBranchIds;

        // Base attendance query
        $query = StudentAttendance::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->with([
                'student',
                'branch',
                'teachingSession.classModel',
                'teachingSession.scheduledTutor',
                'teachingSession.actualTutor',
                'recordedBy',
            ]);

        if ($classId && $classId !== 'all') {
            $query->whereHas('teachingSession', fn ($q) => $q->where('class_id', $classId));
        }

        if ($status && $status !== 'all') {
            if (in_array($status, ['hadir', 'present'])) {
                $query->whereIn('status', ['hadir', 'present']);
            } elseif (in_array($status, ['izin', 'excused'])) {
                $query->whereIn('status', ['izin', 'excused']);
            } elseif (in_array($status, ['sakit'])) {
                $query->whereIn('status', ['sakit']);
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

        // Summary Metrics
        $metricQuery = StudentAttendance::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds);

        if ($dateFrom) {
            $metricQuery->whereDate('recorded_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $metricQuery->whereDate('recorded_at', '<=', $dateTo);
        }

        $totalAttendanceCount = (clone $metricQuery)->count();
        $presentCount = (clone $metricQuery)->whereIn('status', ['hadir', 'present'])->count();
        $permissionCount = (clone $metricQuery)->whereIn('status', ['izin', 'excused'])->count();
        $sickCount = (clone $metricQuery)->whereIn('status', ['sakit'])->count();
        $alpaCount = (clone $metricQuery)->whereIn('status', ['alpa', 'absent'])->count();

        $attendanceRate = $totalAttendanceCount > 0 ? round(($presentCount / $totalAttendanceCount) * 100) : 100;

        // Recent teaching sessions
        $recentSessions = TeachingSession::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->with(['classModel', 'scheduledTutor', 'actualTutor', 'branch'])
            ->withCount('studentAttendances')
            ->latest('session_date')
            ->limit(5)
            ->get();

        $classes = Classes::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $effectiveBranchIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.attendances.index', compact(
            'tenant',
            'attendances',
            'accessibleBranches',
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
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $session->tenant_id !== $tenant->id || ! $user->hasBranchAccess($session->branch_id)) {
            abort(404, 'Sesi mengajar tidak ditemukan atau di luar cakupan cabang Anda.');
        }

        $session->load([
            'classModel',
            'scheduledTutor',
            'actualTutor',
            'branch',
            'studentAttendances.student',
        ]);

        return view('admin.attendances.session', compact('tenant', 'session'));
    }

    public function update(UpdateAttendanceRequest $request, StudentAttendance $attendance): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $attendance->tenant_id !== $tenant->id || ! $user->hasBranchAccess($attendance->branch_id)) {
            abort(404, 'Data presensi tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('update', $attendance);

        $validated = $request->validated();

        $attendance->update([
            'status' => $validated['status'],
            'note' => $validated['note'] ?? $attendance->note,
        ]);

        return back()->with('success', 'Presensi kehadiran siswa berhasil diperbarui.');
    }

    public function replaceTutor(
        ReplaceTutorRequest $request,
        TeachingSession $session,
        TutorReplacementService $replacementService
    ): RedirectResponse {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $session->tenant_id !== $tenant->id || ! $user->hasBranchAccess($session->branch_id)) {
            abort(404, 'Sesi mengajar tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('replaceTutor', $session);

        $validated = $request->validated();

        $replacementService->replaceTutor(
            $session,
            $validated['replacement_tutor_id'],
            $validated['reason'],
            $user
        );

        return back()->with('success', 'Tutor pengganti berhasil ditugaskan pada sesi ini.');
    }
}
