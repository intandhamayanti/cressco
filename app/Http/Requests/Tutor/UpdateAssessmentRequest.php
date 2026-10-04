<?php

namespace App\Http\Requests\Tutor;

use App\Models\Assessment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Assessment|null $assessment */
        $assessment = $this->route('assessment');

        if (! $user || ! $user->isTutor() || ! $assessment) {
            return false;
        }

        if ($assessment->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $user->canTeachClass($assessment->class_id);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
            'name' => 'Nama Penilaian / Judul',
            'type' => 'Jenis Penilaian',
            'material' => 'Materi / Bab',
            'assessment_date' => 'Tanggal Penilaian',
            'max_score' => 'Nilai Maksimal',
            'notes' => 'Catatan Tambahan',
        ];
    }
}
