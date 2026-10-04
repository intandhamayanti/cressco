<?php

namespace App\Http\Requests\Owner;

use App\Services\TenantContext;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassRequest extends FormRequest
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
            'branch_id' => [
                'required',
                'uuid',
                Rule::exists('branches', 'id')->where('tenant_id', $tenantId),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'subject' => [
                'nullable',
                'string',
                'max:150',
            ],
            'level' => [
                'nullable',
                'string',
                'max:100',
            ],
            'capacity' => [
                'nullable',
                'integer',
                'min:1',
                'max:200',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive']),
            ],
            'tutor_id' => [
                'nullable',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)
                        ->whereIn('role', ['tutor', 'admin', 'owner']);
                }),
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
            'branch_id' => 'Cabang',
            'name' => 'Nama Kelas',
            'subject' => 'Mata Pelajaran',
            'level' => 'Tingkat / Level',
            'capacity' => 'Kapasitas Kelas',
            'status' => 'Status',
            'tutor_id' => 'Tutor Pengajar',
        ];
    }
}
