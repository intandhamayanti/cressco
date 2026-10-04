<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassRequest extends FormRequest
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
        $tenantId = $user?->tenant_id;
        $accessibleBranchIds = $user ? $user->accessibleBranchIds()->all() : [];

        return [
            'branch_id' => [
                'required',
                'uuid',
                Rule::in($accessibleBranchIds),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'subject' => [
                'nullable',
                'string',
                'max:150',
            ],
            'level' => [
                'nullable',
                'string',
                'max:100',
            ],
            'capacity' => [
                'nullable',
                'integer',
                'min:1',
                'max:200',
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive']),
            ],
            'tutor_id' => [
                'nullable',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) use ($tenantId) {
                    $query->where('tenant_id', $tenantId)
                        ->whereIn('role', ['tutor', 'admin', 'owner']);
                }),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'branch_id' => 'Cabang',
            'name' => 'Nama Kelas',
            'subject' => 'Mata Pelajaran',
            'level' => 'Tingkat / Level',
            'capacity' => 'Kapasitas Kelas',
            'status' => 'Status',
            'tutor_id' => 'Tutor Pengajar',
        ];
    }
}
