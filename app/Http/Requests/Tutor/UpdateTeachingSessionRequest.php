<?php

namespace App\Http\Requests\Tutor;

use App\Models\TeachingSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeachingSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var TeachingSession|null $session */
        $session = $this->route('session');

        if (! $user || ! $user->isTutor() || ! $session) {
            return false;
        }

        return $user->canAccessTeachingSession($session);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'material' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', Rule::in(['scheduled', 'completed', 'cancelled'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'material' => 'Materi Pembelajaran',
            'notes' => 'Catatan Sesi',
            'status' => 'Status Sesi',
        ];
    }
}
