<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $class = $this->route('class');

        if (! $user || ! $user->isAdmin() || ! $class) {
            return false;
        }

        return $class->tenant_id === $user->tenant_id && $user->hasBranchAccess($class->branch_id);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'scheduled_tutor_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId);
                }),
            ],
            'day_of_week' => [
                'required',
                'integer',
                'between:0,6',
            ],
            'start_time' => [
                'required',
                'date_format:H:i',
            ],
            'end_time' => [
                'required',
                'date_format:H:i',
                'after:start_time',
            ],
            'room' => [
                'nullable',
                'string',
                'max:150',
            ],
            'starts_on' => [
                'required',
                'date',
            ],
            'ends_on' => [
                'nullable',
                'date',
                'after_or_equal:starts_on',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'scheduled_tutor_id' => 'Tutor Terjadwal',
            'day_of_week' => 'Hari Mengajar',
            'start_time' => 'Jam Mulai',
            'end_time' => 'Jam Selesai',
            'room' => 'Ruangan',
            'starts_on' => 'Tanggal Berlaku Mulai',
            'ends_on' => 'Tanggal Berakhir',
            'status' => 'Status Jadwal',
        ];
    }
}
