<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\ReportAbsenceRequest;
use App\Http\Requests\Tutor\UpdateTeachingSessionRequest;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\TeachingSession;
use App\Models\TutorAssignment;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TutorSessionController extends Controller
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

        $tutorBranches = Branch::where('tenant_id', $tenant->id)
            ->whereHas('classes.tutorAssignments', function ($q) use ($user) {
                $q->where('tutor_id', $user->id);
            })
            ->orderBy('name')
            ->get();

        $search = $request->query('search');
        $selectedBranch = $request->query('branch_id', 'all');
        $selectedClass = $request->query('class_id', 'all');
        $selectedStatus = $request->query('status', 'all');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        // Query sessions in tutor's teaching scope
        $query = TeachingSession::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($user, $assignedClassIds) {
                $q->where('scheduled_tutor_id', $user->id)
                    ->orWhere('actual_tutor_id', $user->id)
                    ->orWhereIn('class_id', $assignedClassIds);
            })
            ->with([
                'classModel.branch',
                'scheduledTutor',
                'actualTutor',
                'branch',
                'tutorAttendance',
            ])
            ->withCount('studentAttendances');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('room', 'like', "%{$search}%")
                    ->orWhere('material', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('classModel', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('subject', 'like', "%{$search}%");
                    });
            });
        }

        if ($selectedBranch !== 'all') {
            $query->where('branch_id', $selectedBranch);
        }

        if ($selectedClass !== 'all') {
            $query->where('class_id', $selectedClass);
        }

        if ($selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        if ($dateFrom) {
            $query->whereDate('session_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('session_date', '<=', $dateTo);
        }

        $sessions = $query->orderByDesc('session_date')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        // Metrics for summary cards
        $metricsBase = TeachingSession::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($user, $assignedClassIds) {
                $q->where('scheduled_tutor_id', $user->id)
                    ->orWhere('actual_tutor_id', $user->id)
                    ->orWhereIn('class_id', $assignedClassIds);
            });

        $totalSessions = (clone $metricsBase)->count();
        $completedSessions = (clone $metricsBase)->where('status', 'completed')->count();
        $scheduledSessions = (clone $metricsBase)->where('status', 'scheduled')->count();

        $tutorClasses = Classes::where('tenant_id', $tenant->id)
            ->whereIn('id', $assignedClassIds)
            ->orderBy('name')
            ->get();

        return view('tutor.sessions.index', compact(
            'tenant',
            'user',
            'sessions',
            'tutorBranches',
            'tutorClasses',
            'search',
            'selectedBranch',
            'selectedClass',
            'selectedStatus',
            'dateFrom',
            'dateTo',
            'totalSessions',
            'completedSessions',
            'scheduledSessions'
        ));
    }

    public function show(Request $request, TeachingSession $session): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $session->tenant_id !== $tenant->id || ! $user->canAccessTeachingSession($session)) {
            abort(404, 'Sesi mengajar tidak ditemukan atau di luar cakupan Anda.');
        }

        Gate::authorize('view', $session);

        $session->load([
            'classModel.branch',
            'classModel.enrollments' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)
                    ->where('status', 'active')
                    ->with('student');
            },
            'scheduledTutor',
            'actualTutor',
            'branch',
            'studentAttendances.student',
            'tutorAttendance',
        ]);

        // Build enrolled student attendance list
        $enrolledStudents = $session->classModel?->enrollments->map(function ($enrollment) use ($session) {
            $student = $enrollment->student;
            $attendance = $session->studentAttendances->firstWhere('student_id', $student->id);

            return [
                'student' => $student,
                'attendance' => $attendance,
                'status' => $attendance?->status ?? 'hadir',
                'note' => $attendance?->note ?? '',
            ];
        }) ?? collect();

        // If attendance already recorded, also include students who might have attendance record but inactive enrollment
        $recordedStudentIds = $session->studentAttendances->pluck('student_id')->all();
        $missingStudents = $session->studentAttendances->filter(function ($att) use ($enrolledStudents) {
            return ! $enrolledStudents->contains(fn ($item) => $item['student']->id === $att->student_id);
        })->map(function ($att) {
            return [
                'student' => $att->student,
                'attendance' => $att,
                'status' => $att->status,
                'note' => $att->note,
            ];
        });

        $allStudentRows = $enrolledStudents->concat($missingStudents);

        return view('tutor.sessions.show', compact(
            'tenant',
            'user',
            'session',
            'allStudentRows'
        ));
    }

    public function update(UpdateTeachingSessionRequest $request, TeachingSession $session): RedirectResponse
    {
        $validated = $request->validated();

        $dataToUpdate = [];
        if (array_key_exists('material', $validated)) {
            $dataToUpdate['material'] = $validated['material'];
        }
        if (array_key_exists('notes', $validated)) {
            $dataToUpdate['notes'] = $validated['notes'];
        }
        if (! empty($validated['status'])) {
            $dataToUpdate['status'] = $validated['status'];
        }

        $session->update($dataToUpdate);

        return redirect()->route('tutor.sessions.show', $session->id)
            ->with('success', 'Materi dan catatan sesi mengajar berhasil diperbarui.');
    }

    public function reportAbsence(ReportAbsenceRequest $request, TeachingSession $session): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $session->tenant_id !== $tenant->id) {
            abort(404, 'Sesi mengajar tidak ditemukan.');
        }

        if ($session->status !== 'scheduled') {
            return back()->with('error', 'Hanya sesi dengan status terjadwal yang dapat dilaporkan berhalangan.');
        }

        $validated = $request->validated();
        $reason = ! empty($validated['reason']) ? trim($validated['reason']) : 'Tutor berhalangan hadir';

        $timestamp = now()->translatedFormat('d M Y H:i');
        $absenceNote = "[TUTOR BERHALANGAN] {$user->name}: {$reason} (Dilaporkan pada {$timestamp})";
        $updatedNotes = $session->notes ? $session->notes."\n".$absenceNote : $absenceNote;

        $session->update([
            'actual_tutor_id' => null,
            'notes' => $updatedNotes,
        ]);

        return back()->with('success', 'Laporan ketidakhadiran berhasil dikirim. Admin akan mengatur tutor pengganti untuk sesi ini.');
    }
}
