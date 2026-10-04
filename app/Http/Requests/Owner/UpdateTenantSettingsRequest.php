<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantSettingsRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'notifications_enabled' => ['nullable', 'boolean'],
            'language' => ['nullable', 'string', 'in:id,en'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Bimbel',
            'email' => 'Email Resmi Bimbel',
            'phone' => 'Nomor Telepon Kantor',
            'address' => 'Alamat Kantor Pusat',
            'description' => 'Deskripsi Bimbel',
            'notifications_enabled' => 'Notifikasi',
            'language' => 'Bahasa Aplikasi',
        ];
    }
}
