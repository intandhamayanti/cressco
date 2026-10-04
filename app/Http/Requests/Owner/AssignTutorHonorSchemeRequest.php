<?php

namespace App\Http\Requests\Owner;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTutorHonorSchemeRequest extends FormRequest
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
            'honor_scheme_id' => [
                'nullable',
                'uuid',
                Rule::exists('honor_schemes', 'id')->where('tenant_id', $tenantId),
            ],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'honor_scheme_id' => 'Skema Honor',
            'effective_from' => 'Tanggal Mulai Berlaku',
            'effective_until' => 'Tanggal Berakhir Berlaku',
        ];
    }
}
