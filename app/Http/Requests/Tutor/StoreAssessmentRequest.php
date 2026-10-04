<?php

namespace App\Http\Requests\Tutor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $classId = $this->input('class_id');

        if (! $user || ! $user->isTutor() || ! $classId) {
            return false;
        }

        return $user->canTeachClass($classId);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'uuid', 'exists:classes,id'],
            'name' => ['required', 'string', 'max:200'],
            'type' => ['required', 'string', Rule::in(['tugas', 'quiz', 'ujian'])],
            'material' => ['nullable', 'string', 'max:1000'],
            'assessment_date' => ['required', 'date'],
            'max_score' => ['required', 'numeric', 'min:1', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'class_id' => 'Kelas',
            'name' => 'Nama Penilaian / Judul',
            'type' => 'Jenis Penilaian',
            'material' => 'Materi / Bab',
            'assessment_date' => 'Tanggal Penilaian',
            'max_score' => 'Nilai Maksimal',
            'notes' => 'Catatan Tambahan',
        ];
    }
}
