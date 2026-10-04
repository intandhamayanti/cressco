<?php

namespace App\Services;

use App\Models\TeachingSession;
use App\Models\TutorAttendance;
use App\Models\TutorReplacement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class TutorReplacementService
{
    /**
     * Replace the tutor of a teaching session and record the replacement audit trail.
     *
     * @throws InvalidArgumentException
     */
    public function replaceTutor(
        TeachingSession $session,
        User|string $replacementTutor,
        string $reason,
        User $actor
    ): TutorReplacement {
        $replacementTutorModel = $replacementTutor instanceof User
            ? $replacementTutor
            : User::query()->where('id', $replacementTutor)->where('tenant_id', $session->tenant_id)->first();

        if (! $replacementTutorModel) {
            throw new InvalidArgumentException('Replacement tutor not found in this tenant.');
        }

        if ($replacementTutorModel->tenant_id !== $session->tenant_id) {
            throw new InvalidArgumentException('Replacement tutor does not belong to the same tenant.');
        }

        if ($replacementTutorModel->status !== 'active') {
            throw new InvalidArgumentException('Replacement tutor must be an active tutor.');
        }

        $previousActualTutorId = $session->actual_tutor_id ?? $session->scheduled_tutor_id;

        return DB::transaction(function () use (
            $session,
            $replacementTutorModel,
            $previousActualTutorId,
            $reason,
            $actor
        ) {
            $now = Carbon::now();

            $replacement = TutorReplacement::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $session->tenant_id,
                'branch_id' => $session->branch_id,
                'teaching_session_id' => $session->id,
                'scheduled_tutor_id' => $session->scheduled_tutor_id,
                'previous_actual_tutor_id' => $previousActualTutorId,
                'replacement_tutor_id' => $replacementTutorModel->id,
                'reason' => $reason,
                'changed_by' => $actor->id,
                'changed_at' => $now,
            ]);

            // Update session actual tutor
            $session->update([
                'actual_tutor_id' => $replacementTutorModel->id,
            ]);

            // If tutor attendance record exists for this session, update tutor_id to replacement tutor
            TutorAttendance::where('teaching_session_id', $session->id)
                ->where('tenant_id', $session->tenant_id)
                ->update([
                    'tutor_id' => $replacementTutorModel->id,
                ]);

            return $replacement;
        });
    }
}
