<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && $user->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();
        $accessibleBranchIds = $user ? $user->accessibleBranchIds()->all() : [];

        return [
            'name' => ['required', 'string', 'max:150'],
            'branch_id' => [
                'required',
                'uuid',
                Rule::in($accessibleBranchIds),
            ],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', Rule::in(['Laki-laki', 'Perempuan'])],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'parent_name' => ['nullable', 'string', 'max:150'],
            'parent_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'joined_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama siswa wajib diisi.',
            'name.max' => 'Nama siswa maksimal 150 karakter.',
            'branch_id.required' => 'Cabang siswa wajib dipilih.',
            'branch_id.in' => 'Cabang yang dipilih tidak valid atau di luar hak akses Anda.',
            'gender.in' => 'Pilihan jenis kelamin tidak valid.',
            'phone.max' => 'Nomor telepon maksimal 50 karakter.',
            'parent_name.max' => 'Nama orang tua maksimal 150 karakter.',
            'parent_phone.max' => 'Nomor telepon orang tua maksimal 50 karakter.',
            'status.in' => 'Status siswa tidak valid.',
        ];
    }
}
