<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHonorSchemeRequest extends FormRequest
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
            'method' => ['required', 'string', Rule::in(['per_session', 'per_student', 'revenue_share', 'fixed_monthly'])],
            'rate' => ['nullable', 'numeric', 'min:0'],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fixed_amount' => ['nullable', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Skema Honor',
            'method' => 'Metode Penggajian',
            'rate' => 'Tarif / Rate',
            'percentage' => 'Persentase Bagi Hasil',
            'fixed_amount' => 'Nominal Gaji Tetap',
            'effective_from' => 'Tanggal Mulai Berlaku',
            'effective_until' => 'Tanggal Berakhir Berlaku',
            'status' => 'Status',
            'is_default' => 'Jadikan Skema Utama Tenant',
        ];
    }
}
