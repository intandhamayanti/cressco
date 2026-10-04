<?php

namespace App\Http\Requests\Tutor;

use App\Models\StudentAttendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var StudentAttendance|null $attendance */
        $attendance = $this->route('attendance');

        if (! $user || ! $user->isTutor() || ! $attendance) {
            return false;
        }

        if ($attendance->tenant_id !== $user->tenant_id) {
            return false;
        }

        $session = $attendance->teachingSession;

        return $session ? $user->canAccessTeachingSession($session) : false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['hadir', 'izin', 'sakit', 'alpa'])],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'Status Kehadiran',
            'note' => 'Catatan / Alasan',
        ];
    }
}
