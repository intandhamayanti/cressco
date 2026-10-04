<?php

namespace App\Http\Requests\Admin;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTutorRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'branch_id' => [
                'nullable',
                'uuid',
                Rule::in($accessibleBranchIds),
            ],
            'class_id' => [
                'nullable',
                'uuid',
                Rule::exists('classes', 'id')
                    ->where('tenant_id', $tenantId)
                    ->whereIn('branch_id', $accessibleBranchIds),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Tutor',
            'email' => 'Email',
            'phone' => 'Nomor WhatsApp / Telepon',
            'password' => 'Password',
            'status' => 'Status',
            'branch_id' => 'Cabang',
            'class_id' => 'Penugasan Kelas Awal',
        ];
    }
}
