<?php

namespace App\Http\Requests\Owner;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $branch = $this->route('branch');

        if (! $branch instanceof Branch) {
            $branch = Branch::where('id', $branch)->where('tenant_id', $this->user()?->tenant_id)->first();
        }

        return $this->user()
            && $this->user()->isOwner()
            && $branch
            && $branch->tenant_id === $this->user()->tenant_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;
        $branch = $this->route('branch');
        $branchId = $branch instanceof Branch ? $branch->id : $branch;

        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('branches', 'code')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($branchId),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama cabang wajib diisi.',
            'name.max' => 'Nama cabang maksimal 150 karakter.',
            'code.unique' => 'Kode cabang ini sudah digunakan di bimbel Anda.',
            'code.max' => 'Kode cabang maksimal 50 karakter.',
            'status.required' => 'Status cabang wajib dipilih.',
            'status.in' => 'Status cabang tidak valid.',
        ];
    }
}
