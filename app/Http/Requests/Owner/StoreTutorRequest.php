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
        $honorMode = $this->input('honor_mode');
        $hasSchemeId = $this->filled('honor_scheme_id');
        $hasMethod = $this->filled('method');
        $isDefaultMode = $honorMode === 'default' || (! $this->filled('honor_mode') && ! $hasSchemeId && ! $hasMethod);

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
            'honor_mode' => ['nullable', 'string', 'in:default,other'],
            'method' => [
                Rule::requiredIf(! $isDefaultMode && ! $hasSchemeId),
                'nullable',
                'string',
                'in:per_session,per_student,fixed_monthly,revenue_share',
            ],
            'rate' => [
                Rule::requiredIf(! $isDefaultMode && ! $hasSchemeId),
                'nullable',
                'numeric',
                'min:0',
            ],
            'effective_from' => [
                Rule::requiredIf(! $isDefaultMode && ! $hasSchemeId),
                'nullable',
                'date',
            ],
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
            'honor_mode' => 'Pilihan Pengaturan Honor',
            'method' => 'Metode Honor',
            'rate' => 'Tarif Honor',
            'effective_from' => 'Tanggal Mulai Berlaku',
            'honor_scheme_id' => 'Pengaturan Honor',
            'class_id' => 'Kelas Awal',
        ];
    }
}
