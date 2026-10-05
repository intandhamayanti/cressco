<?php

namespace App\Http\Requests\Tutor;

use App\Models\TeachingSession;
use Illuminate\Foundation\Http\FormRequest;

class ReportAbsenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var TeachingSession|null $session */
        $session = $this->route('session');

        if (! $user || ! $session) {
            return false;
        }

        if ($session->tenant_id !== $user->tenant_id) {
            return false;
        }

        if (! $user->isTutor()) {
            return false;
        }

        // Tutor must be the scheduled tutor or current actual tutor of the session
        return $session->scheduled_tutor_id === $user->id || $session->actual_tutor_id === $user->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'reason' => 'Alasan Berhalangan',
        ];
    }
}
