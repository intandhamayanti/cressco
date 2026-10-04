<?php

namespace App\Http\Requests\Owner;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTutorRequest extends FormRequest
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
            'honor_scheme_id' => [
                'nullable',
                'uuid',
                Rule::exists('honor_schemes', 'id')->where('tenant_id', $tenantId),
            ],
            'class_id' => [
                'nullable',
                'uuid',
                Rule::exists('classes', 'id')->where('tenant_id', $tenantId),
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
            'honor_scheme_id' => 'Skema Honor',
            'class_id' => 'Kelas Awal',
        ];
    }
}
