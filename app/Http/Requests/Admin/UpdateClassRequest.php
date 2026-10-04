<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassRequest extends FormRequest
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
        $user = $this->user();
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
                'required',
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
            'branch_id' => 'Cabang',
            'name' => 'Nama Kelas',
            'subject' => 'Mata Pelajaran',
            'level' => 'Tingkat / Level',
            'capacity' => 'Kapasitas Kelas',
            'status' => 'Status',
        ];
    }
}
