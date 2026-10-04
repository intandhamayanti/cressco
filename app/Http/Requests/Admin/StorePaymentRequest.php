<?php

namespace App\Http\Requests\Admin;

use App\Services\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
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
        $accessibleBranchIds = $this->user()->branches()->pluck('branches.id')->toArray();

        return [
            'student_id' => [
                'required',
                'uuid',
                Rule::exists('students', 'id')->where('tenant_id', $tenantId),
            ],
            'branch_id' => [
                'required',
                'uuid',
                Rule::exists('branches', 'id')->where('tenant_id', $tenantId),
                Rule::in($accessibleBranchIds),
            ],
            'enrollment_id' => [
                'nullable',
                'uuid',
                Rule::exists('enrollments', 'id')->where('tenant_id', $tenantId),
            ],
            'period' => ['required', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'status' => ['required', 'string', Rule::in(['belum_bayar', 'menunggu_verifikasi', 'lunas', 'terlambat'])],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'proof' => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'Siswa',
            'branch_id' => 'Cabang',
            'enrollment_id' => 'Kelas / Enrollment',
            'period' => 'Periode Tagihan',
            'amount' => 'Nominal Tagihan',
            'due_date' => 'Tanggal Jatuh Tempo',
            'status' => 'Status Pembayaran',
            'paid_at' => 'Waktu Pembayaran',
            'notes' => 'Catatan',
        ];
    }
}
