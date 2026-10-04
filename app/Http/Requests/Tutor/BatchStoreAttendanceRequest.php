<?php

namespace App\Http\Requests\Tutor;

use App\Models\TeachingSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchStoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var TeachingSession|null $session */
        $session = $this->route('session');

        if (! $user || ! $user->isTutor() || ! $session) {
            return false;
        }

        return $user->canAccessTeachingSession($session);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'uuid', 'exists:students,id'],
            'attendances.*.status' => ['required', 'string', Rule::in(['hadir', 'izin', 'sakit', 'alpa'])],
            'attendances.*.note' => ['nullable', 'string', 'max:500'],
            'material' => ['nullable', 'string', 'max:2000'],
            'session_notes' => ['nullable', 'string', 'max:2000'],
            'mark_session_completed' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'attendances' => 'Daftar Presensi Siswa',
            'attendances.*.student_id' => 'ID Siswa',
            'attendances.*.status' => 'Status Kehadiran',
            'attendances.*.note' => 'Catatan Siswa',
            'material' => 'Materi Pembelajaran',
            'session_notes' => 'Catatan Sesi',
        ];
    }
}
