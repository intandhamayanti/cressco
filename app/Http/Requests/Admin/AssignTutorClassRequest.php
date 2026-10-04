<?php

namespace App\Http\Requests\Admin;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTutorClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = TenantContext::getTenantId() ?? $this->user()->tenant_id;
        $accessibleBranchIds = $this->user()->accessibleBranchIds()->all();

        return [
            'class_id' => [
                'required',
                'uuid',
                Rule::exists('classes', 'id')
                    ->where('tenant_id', $tenantId)
                    ->whereIn('branch_id', $accessibleBranchIds),
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
            'class_id' => 'Kelas',
            'started_at' => 'Tanggal Mulai',
            'ended_at' => 'Tanggal Selesai',
            'status' => 'Status Penugasan',
        ];
    }
}
