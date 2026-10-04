<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTutorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $class = $this->route('class');

        if (! $user || ! $user->isAdmin() || ! $class) {
            return false;
        }

        return $class->tenant_id === $user->tenant_id && $user->hasBranchAccess($class->branch_id);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tenantId = $this->user()?->tenant_id;

        return [
            'tutor_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)
                        ->whereIn('role', ['tutor', 'admin', 'owner']);
                }),
            ],
            'started_at' => [
                'nullable',
                'date',
            ],
            'ended_at' => [
                'nullable',
                'date',
                'after_or_equal:started_at',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive']),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tutor_id' => 'Tutor Pengajar',
            'started_at' => 'Tanggal Mulai',
            'ended_at' => 'Tanggal Selesai',
            'status' => 'Status Penugasan',
        ];
    }
}
