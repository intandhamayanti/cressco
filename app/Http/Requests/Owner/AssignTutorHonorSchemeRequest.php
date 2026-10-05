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
        $honorMode = $this->input('honor_mode');
        $hasSchemeId = $this->filled('honor_scheme_id');
        $hasMethod = $this->filled('method');

        $isDefaultMode = $honorMode === 'default' || (! $this->filled('honor_mode') && ! $hasSchemeId && ! $hasMethod);

        return [
            'honor_mode' => ['nullable', 'string', 'in:default,other,custom'],
            'honor_scheme_id' => [
                'nullable',
                'uuid',
                Rule::exists('honor_schemes', 'id')->where('tenant_id', $tenantId),
            ],
            'method' => [
                Rule::requiredIf(! $isDefaultMode && ! $hasSchemeId),
                'nullable',
                'string',
                'in:per_session,per_student,fixed_monthly,revenue_share',
            ],
            'rate' => [
                Rule::requiredIf(! $isDefaultMode && ! $hasSchemeId && in_array($this->input('method'), ['per_session', 'per_student', 'fixed_monthly'])),
                'nullable',
                'numeric',
                'min:0',
            ],
            'effective_from' => [
                Rule::requiredIf(! $isDefaultMode),
                'nullable',
                'date',
            ],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'honor_mode' => 'Pilihan Pengaturan Honor',
            'honor_scheme_id' => 'Pengaturan Honor',
            'method' => 'Metode Honor',
            'rate' => 'Nominal Honor',
            'effective_from' => 'Tanggal Mulai Berlaku',
            'effective_until' => 'Tanggal Berakhir Berlaku',
        ];
    }
}
