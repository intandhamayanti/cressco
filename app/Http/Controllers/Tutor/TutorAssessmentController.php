<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\StoreAssessmentRequest;
use App\Http\Requests\Tutor\StoreAssessmentResultsRequest;
use App\Http\Requests\Tutor\UpdateAssessmentRequest;
use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\TutorAssignment;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TutorAssessmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', Assessment::class);

        $assignedClassIds = TutorAssignment::where('tenant_id', $tenant->id)
            ->where('tutor_id', $user->id)
            ->pluck('class_id')
            ->all();

        $tutorClasses = Classes::where('tenant_id', $tenant->id)
            ->whereIn('id', $assignedClassIds)
            ->with('branch')
            ->orderBy('name')
            ->get();

        $tutorBranches = Branch::where('tenant_id', $tenant->id)
            ->whereIn('id', $tutorClasses->pluck('branch_id')->unique()->filter())
            ->orderBy('name')
            ->get();

        $search = $request->query('search');
        $selectedBranch = $request->query('branch_id', 'all');
        $selectedClass = $request->query('class_id', 'all');
        $selectedType = $request->query('type', 'all');

        $query = Assessment::where('tenant_id', $tenant->id)
            ->whereIn('class_id', $assignedClassIds)
            ->with(['class.branch', 'createdBy'])
            ->withCount('results')
            ->withAvg('results', 'score');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('material', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('class', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($selectedBranch !== 'all') {
            $query->where('branch_id', $selectedBranch);
        }

        if ($selectedClass !== 'all') {
            $query->where('class_id', $selectedClass);
        }

        if ($selectedType !== 'all') {
            $query->where('type', $selectedType);
        }

        $assessments = $query->latest('assessment_date')->paginate(12)->withQueryString();

        // Metrics
        $totalAssessments = Assessment::where('tenant_id', $tenant->id)->whereIn('class_id', $assignedClassIds)->count();
        $totalResultsSubmitted = AssessmentResult::where('tenant_id', $tenant->id)
            ->whereHas('assessment', fn ($q) => $q->whereIn('class_id', $assignedClassIds))
            ->count();
        $overallAvg = AssessmentResult::where('tenant_id', $tenant->id)
            ->whereHas('assessment', fn ($q) => $q->whereIn('class_id', $assignedClassIds))
            ->avg('score');

        return view('tutor.assessments.index', compact(
            'tenant',
            'user',
            'assessments',
            'tutorClasses',
            'tutorBranches',
            'search',
            'selectedBranch',
            'selectedClass',
            'selectedType',
            'totalAssessments',
            'totalResultsSubmitted',
            'overallAvg'
        ));
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('create', Assessment::class);

        $assignedClassIds = TutorAssignment::where('tenant_id', $tenant->id)
            ->where('tutor_id', $user->id)
            ->where('status', 'active')
            ->pluck('class_id')
            ->all();

        $classes = Classes::where('tenant_id', $tenant->id)
            ->whereIn('id', $assignedClassIds)
            ->with('branch')
            ->orderBy('name')
            ->get();

        $preselectedClassId = $request->query('class_id');

        return view('tutor.assessments.create', compact('tenant', 'classes', 'preselectedClassId'));
    }

    public function store(StoreAssessmentRequest $request): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        $validated = $request->validated();
        $class = Classes::findOrFail($validated['class_id']);

        if ($class->tenant_id !== $tenant->id || ! $user->canTeachClass($class->id)) {
            abort(403, 'Anda tidak memiliki akses ke kelas ini.');
        }

        $assessment = Assessment::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $class->branch_id,
            'class_id' => $class->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'material' => $validated['material'] ?? null,
            'assessment_date' => $validated['assessment_date'],
            'max_score' => $validated['max_score'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user->id,
        ]);

        return redirect()->route('tutor.assessments.show', $assessment->id)
            ->with('success', 'Penilaian baru berhasil dibuat. Silakan input nilai siswa.');
    }

    public function show(Request $request, Assessment $assessment): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $assessment->tenant_id !== $tenant->id || ! $user->canTeachClass($assessment->class_id)) {
            abort(404, 'Penilaian tidak ditemukan atau di luar cakupan mengajar Anda.');
        }

        Gate::authorize('view', $assessment);

        $assessment->load([
            'class.branch',
            'class.enrollments' => fn ($q) => $q->where('tenant_id', $tenant->id)->where('status', 'active')->with('student'),
            'results.student',
            'createdBy',
        ]);

        // Merge active enrolled students with existing results
        $enrolledStudents = $assessment->class?->enrollments->map(function ($enrollment) use ($assessment) {
            $student = $enrollment->student;
            $result = $assessment->results->firstWhere('student_id', $student->id);

            return [
                'student' => $student,
                'result' => $result,
                'score' => $result?->score,
                'notes' => $result?->notes ?? '',
            ];
        }) ?? collect();

        $recordedStudentIds = $assessment->results->pluck('student_id')->all();
        $missingStudents = $assessment->results->filter(function ($res) use ($enrolledStudents) {
            return ! $enrolledStudents->contains(fn ($item) => $item['student']->id === $res->student_id);
        })->map(function ($res) {
            return [
                'student' => $res->student,
                'result' => $res,
                'score' => $res->score,
                'notes' => $res->notes ?? '',
            ];
        });

        $allStudentRows = $enrolledStudents->concat($missingStudents);

        $avgScore = $assessment->results->avg('score');
        $highestScore = $assessment->results->max('score');
        $lowestScore = $assessment->results->min('score');

        return view('tutor.assessments.show', compact(
            'tenant',
            'user',
            'assessment',
            'allStudentRows',
            'avgScore',
            'highestScore',
            'lowestScore'
        ));
    }

    public function edit(Request $request, Assessment $assessment): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $assessment->tenant_id !== $tenant->id || ! $user->canTeachClass($assessment->class_id)) {
            abort(404, 'Penilaian tidak ditemukan atau di luar cakupan mengajar Anda.');
        }

        Gate::authorize('update', $assessment);

        return view('tutor.assessments.edit', compact('tenant', 'assessment'));
    }

    public function update(UpdateAssessmentRequest $request, Assessment $assessment): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $assessment->tenant_id !== $tenant->id || ! $user->canTeachClass($assessment->class_id)) {
            abort(404, 'Penilaian tidak ditemukan.');
        }

        Gate::authorize('update', $assessment);

        $validated = $request->validated();
        $assessment->update($validated);

        return redirect()->route('tutor.assessments.show', $assessment->id)
            ->with('success', 'Informasi penilaian berhasil diperbarui.');
    }

    public function storeResults(StoreAssessmentResultsRequest $request, Assessment $assessment): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $assessment->tenant_id !== $tenant->id || ! $user->canTeachClass($assessment->class_id)) {
            abort(404, 'Penilaian tidak ditemukan.');
        }

        Gate::authorize('update', $assessment);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $assessment, $tenant) {
            foreach ($validated['results'] as $item) {
                if (isset($item['score']) && $item['score'] !== '') {
                    AssessmentResult::updateOrCreate(
                        [
                            'tenant_id' => $tenant->id,
                            'assessment_id' => $assessment->id,
                            'student_id' => $item['student_id'],
                        ],
                        [
                            'branch_id' => $assessment->branch_id,
                            'score' => $item['score'],
                            'notes' => $item['notes'] ?? null,
                        ]
                    );
                }
            }
        });

        return redirect()->route('tutor.assessments.show', $assessment->id)
            ->with('success', 'Nilai penilaian siswa berhasil disimpan.');
    }

    public function destroy(Request $request, Assessment $assessment): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $assessment->tenant_id !== $tenant->id || ! $user->canTeachClass($assessment->class_id)) {
            abort(404, 'Penilaian tidak ditemukan.');
        }

        Gate::authorize('delete', $assessment);

        DB::transaction(function () use ($assessment) {
            $assessment->results()->delete();
            $assessment->delete();
        });

        return redirect()->route('tutor.assessments.index')
            ->with('success', 'Data penilaian berhasil dihapus.');
    }
}
