<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
        $tenantId = $this->user()->tenant_id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'role' => ['required', 'string', Rule::in(['admin', 'tutor'])],
            'password' => ['nullable', 'string', 'min:8'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'invited'])],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => [
                'uuid',
                Rule::exists('branches', 'id')->where(fn ($query) => $query->where('tenant_id', $tenantId)),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi.',
            'name.max' => 'Nama pengguna maksimal 150 karakter.',
            'email.required' => 'Email pengguna wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar dalam sistem.',
            'role.required' => 'Role pengguna wajib dipilih.',
            'role.in' => 'Role yang diperbolehkan hanya Admin atau Tutor.',
            'password.min' => 'Password minimal 8 karakter.',
            'status.in' => 'Status pengguna tidak valid.',
            'branch_ids.array' => 'Data cabang yang dipilih tidak valid.',
            'branch_ids.*.exists' => 'Cabang yang dipilih tidak ditemukan dalam bimbel Anda.',
        ];
    }
}
