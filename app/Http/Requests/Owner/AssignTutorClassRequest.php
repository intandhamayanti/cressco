<?php

namespace App\Http\Requests\Owner;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTutorClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isOwner();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = TenantContext::getTenantId() ?? $this->user()->tenant_id;

        return [
            'class_id' => [
                'required',
                'uuid',
                Rule::exists('classes', 'id')->where('tenant_id', $tenantId),
            ],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'class_id' => 'Kelas Bimbel',
            'started_at' => 'Tanggal Mulai Penugasan',
            'ended_at' => 'Tanggal Selesai Penugasan',
            'status' => 'Status Penugasan',
        ];
    }
}
