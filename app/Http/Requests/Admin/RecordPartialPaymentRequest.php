<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RecordPartialPaymentRequest extends FormRequest
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
        return [
            'amount_paid' => ['required', 'numeric', 'min:1'],
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
            'amount_paid' => 'Nominal Pembayaran',
            'paid_at' => 'Waktu Pembayaran',
            'notes' => 'Catatan',
            'proof' => 'Bukti Pembayaran',
            'payment_proof' => 'Bukti Pembayaran',
        ];
    }
}
