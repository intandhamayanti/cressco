<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $attendance = $this->route('attendance');

        if (! $user || ! $user->isAdmin() || ! $attendance) {
            return false;
        }

        return $attendance->tenant_id === $user->tenant_id && $user->hasBranchAccess($attendance->branch_id);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['hadir', 'izin', 'sakit', 'alpa', 'present', 'absent', 'late', 'excused']),
            ],
            'note' => [
                'nullable',
                'string',
                'max:500',
            ],
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
