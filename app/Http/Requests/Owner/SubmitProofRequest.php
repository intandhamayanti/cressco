<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;

class SubmitProofRequest extends FormRequest
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
            'proof' => ['required_without:payment_proof', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'proof' => 'Bukti Pembayaran',
            'payment_proof' => 'Bukti Pembayaran',
            'notes' => 'Catatan',
        ];
    }
}
