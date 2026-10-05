<?php

namespace App\Http\Requests\Admin;

use App\Models\TeachingSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReplaceTutorRequest extends FormRequest
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

        if ($user->isOwner()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $user->hasBranchAccess($session->branch_id);
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'replacement_tutor_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)
                        ->where('status', 'active');
                }),
            ],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'replacement_tutor_id' => 'Tutor Pengganti',
            'reason' => 'Alasan Penggantian',
        ];
    }
}
