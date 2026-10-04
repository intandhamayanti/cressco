<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\BatchStoreAttendanceRequest;
use App\Http\Requests\Tutor\UpdateStudentAttendanceRequest;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Models\TutorAttendance;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TutorAttendanceController extends Controller
{
    public function store(BatchStoreAttendanceRequest $request, TeachingSession $session): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $session->tenant_id !== $tenant->id || ! $user->canAccessTeachingSession($session)) {
            abort(404, 'Sesi mengajar tidak ditemukan atau di luar cakupan Anda.');
        }

        Gate::authorize('recordAttendance', $session);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $session, $user, $tenant) {
            $now = Carbon::now();

            foreach ($validated['attendances'] as $item) {
                StudentAttendance::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'teaching_session_id' => $session->id,
                        'student_id' => $item['student_id'],
                    ],
                    [
                        'branch_id' => $session->branch_id,
                        'status' => $item['status'],
                        'note' => $item['note'] ?? null,
                        'recorded_at' => $now,
                        'recorded_by' => $user->id,
                    ]
                );
            }

            // Auto record/update tutor attendance for this session
            TutorAttendance::updateOrCreate(
                [
                    'teaching_session_id' => $session->id,
                ],
                [
                    'tenant_id' => $tenant->id,
                    'branch_id' => $session->branch_id,
                    'tutor_id' => $session->actual_tutor_id ?? $user->id,
                    'status' => 'present',
                    'recorded_at' => $now,
                    'source' => 'student_attendance_submission',
                ]
            );

            // Update session material / notes / status if supplied
            $sessionUpdates = [];
            if (! empty($validated['material'])) {
                $sessionUpdates['material'] = $validated['material'];
            }
            if (! empty($validated['session_notes'])) {
                $sessionUpdates['notes'] = $validated['session_notes'];
            }
            if (! empty($validated['mark_session_completed'])) {
                $sessionUpdates['status'] = 'completed';
            }
            if (! $session->actual_tutor_id) {
                $sessionUpdates['actual_tutor_id'] = $user->id;
            }

            if (! empty($sessionUpdates)) {
                $session->update($sessionUpdates);
            }
        });

        return redirect()->route('tutor.sessions.show', $session->id)
            ->with('success', 'Presensi siswa dan kehadiran sesi berhasil disimpan.');
    }

    public function update(UpdateStudentAttendanceRequest $request, StudentAttendance $attendance): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $attendance->tenant_id !== $tenant->id) {
            abort(404, 'Data presensi tidak ditemukan.');
        }

        Gate::authorize('update', $attendance);

        $validated = $request->validated();

        $attendance->update([
            'status' => $validated['status'],
            'note' => $validated['note'] ?? $attendance->note,
        ]);

        return back()->with('success', 'Koreksi presensi siswa berhasil disimpan.');
    }
}
