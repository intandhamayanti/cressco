<?php

namespace App\Http\Requests\Owner;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isOwner();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = TenantContext::getTenantId() ?? $this->user()->tenant_id;

        return [
            'scheduled_tutor_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)
                        ->whereIn('role', ['tutor', 'admin', 'owner']);
                }),
            ],
            'day_of_week' => [
                'required',
                'integer',
                'min:0',
                'max:6',
            ],
            'start_time' => [
                'required',
                'string',
                'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/',
            ],
            'end_time' => [
                'required',
                'string',
                'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/',
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
                'required',
                'string',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'scheduled_tutor_id' => 'Tutor Pengajar',
            'day_of_week' => 'Hari',
            'start_time' => 'Jam Mulai',
            'end_time' => 'Jam Selesai',
            'room' => 'Ruangan / Lab',
            'starts_on' => 'Tanggal Mulai Efektif',
            'ends_on' => 'Tanggal Berakhir Efektif',
            'status' => 'Status',
        ];
    }
}
