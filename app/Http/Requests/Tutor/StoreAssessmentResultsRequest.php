<?php

namespace App\Http\Requests\Tutor;

use App\Models\Assessment;
use Illuminate\Foundation\Http\FormRequest;

class StoreAssessmentResultsRequest extends FormRequest
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
        /** @var Assessment|null $assessment */
        $assessment = $this->route('assessment');
        $maxScore = $assessment ? $assessment->max_score : 100;

        return [
            'results' => ['required', 'array', 'min:1'],
            'results.*.student_id' => ['required', 'uuid', 'exists:students,id'],
            'results.*.score' => ['required', 'numeric', 'min:0', 'max:'.$maxScore],
            'results.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'results' => 'Daftar Nilai Siswa',
            'results.*.student_id' => 'ID Siswa',
            'results.*.score' => 'Nilai',
            'results.*.notes' => 'Catatan Siswa',
        ];
    }
}
