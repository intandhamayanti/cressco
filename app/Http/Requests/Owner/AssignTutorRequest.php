<?php

namespace App\Http\Requests\Owner;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTutorRequest extends FormRequest
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
            'tutor_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)
                        ->whereIn('role', ['tutor', 'admin', 'owner']);
                }),
            ],
            'started_at' => [
                'nullable',
                'date',
            ],
            'ended_at' => [
                'nullable',
                'date',
                'after_or_equal:started_at',
            ],
            'status' => [
                'nullable',
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
            'tutor_id' => 'Tutor Pengajar',
            'started_at' => 'Tanggal Mulai Mengajar',
            'ended_at' => 'Tanggal Selesai Mengajar',
            'status' => 'Status Penugasan',
        ];
    }
}
