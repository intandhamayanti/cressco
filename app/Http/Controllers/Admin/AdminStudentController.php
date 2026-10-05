<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\OwnerImportService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminStudentController extends Controller
{
    public function __construct(
        protected ?OwnerImportService $importService = null
    ) {
        $this->importService = $importService ?? app(OwnerImportService::class);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', Student::class);

        $accessibleBranches = $user->accessibleBranches();
        $accessibleBranchIds = $user->accessibleBranchIds()->all();

        $search = $request->query('search');
        $branchId = $request->query('branch_id', 'all');
        $status = $request->query('status', 'all');

        $query = Student::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->with(['branch', 'enrollments' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with('classModel');
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('parent_name', 'like', "%{$search}%")
                    ->orWhere('parent_phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($branchId && $branchId !== 'all') {
            if (in_array($branchId, $accessibleBranchIds, true)) {
                $query->where('branch_id', $branchId);
            }
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $students = $query->orderBy('name')->get();

        // Metrics strictly scoped to Admin's accessible branches
        $totalStudentsCount = Student::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->count();

        $activeStudentsCount = Student::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'active')
            ->count();

        $inactiveStudentsCount = $totalStudentsCount - $activeStudentsCount;

        // Active classes in accessible branches for multi-class enrollment selector
        $classes = Classes::where('tenant_id', $tenant->id)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'active')
            ->with('branch')
            ->orderBy('name')
            ->get();

        return view('admin.students.index', compact(
            'tenant',
            'students',
            'accessibleBranches',
            'classes',
            'search',
            'branchId',
            'status',
            'totalStudentsCount',
            'activeStudentsCount',
            'inactiveStudentsCount'
        ));
    }

    public function show(Request $request, Student $student): View
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $student->tenant_id !== $tenant->id || ! $user->hasBranchAccess($student->branch_id)) {
            abort(404, 'Siswa tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('view', $student);

        $student->load([
            'branch',
            'enrollments' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->with('classModel');
            },
            'attendances' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->latest('recorded_at')->limit(10);
            },
            'payments' => function ($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id)->latest()->limit(10);
            },
        ]);

        $accessibleBranches = $user->accessibleBranches();

        // Available classes in this student's branch that the student is not actively enrolled in yet
        $activeEnrolledClassIds = $student->enrollments->where('status', 'active')->pluck('class_id')->all();
        $availableClasses = Classes::where('tenant_id', $tenant->id)
            ->where('branch_id', $student->branch_id)
            ->where('status', 'active')
            ->whereNotIn('id', $activeEnrolledClassIds)
            ->orderBy('name')
            ->get();

        return view('admin.students.show', compact('tenant', 'student', 'accessibleBranches', 'availableClasses'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        $validated = $request->validated();

        Gate::authorize('create', [Student::class, $validated['branch_id']]);

        $newStudent = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $validated['branch_id'],
            'name' => $validated['name'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'parent_name' => $validated['parent_name'] ?? null,
            'parent_phone' => $validated['parent_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'joined_at' => $validated['joined_at'] ?? now()->toDateString(),
            'status' => $validated['status'] ?? 'active',
        ]);

        // Multi-Class Enrollment (Optional)
        if (! empty($validated['class_ids']) && is_array($validated['class_ids'])) {
            $uniqueClassIds = array_unique(array_filter($validated['class_ids']));
            foreach ($uniqueClassIds as $classId) {
                $class = Classes::where('tenant_id', $tenant->id)->find($classId);
                if ($class && $user->hasBranchAccess($class->branch_id)) {
                    Enrollment::firstOrCreate([
                        'tenant_id' => $tenant->id,
                        'branch_id' => $class->branch_id,
                        'student_id' => $newStudent->id,
                        'class_id' => $class->id,
                    ], [
                        'id' => (string) Str::uuid(),
                        'started_at' => $newStudent->joined_at ?? now()->toDateString(),
                        'status' => 'active',
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.students.index')
            ->with('success', "Siswa '{$newStudent->name}' berhasil didaftarkan.");
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $student->tenant_id !== $tenant->id || ! $user->hasBranchAccess($student->branch_id)) {
            abort(404, 'Siswa tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('update', $student);

        $validated = $request->validated();

        $student->update([
            'name' => $validated['name'],
            'branch_id' => $validated['branch_id'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'parent_name' => $validated['parent_name'] ?? null,
            'parent_phone' => $validated['parent_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'joined_at' => $validated['joined_at'] ?? $student->joined_at,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->back()
            ->with('success', "Data siswa '{$student->name}' berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, Student $student): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $student->tenant_id !== $tenant->id || ! $user->hasBranchAccess($student->branch_id)) {
            abort(404, 'Siswa tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('update', $student);

        $newStatus = $student->status === 'active' ? 'inactive' : 'active';
        $student->update(['status' => $newStatus]);

        $statusLabel = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Status siswa '{$student->name}' berhasil {$statusLabel}.");
    }

    public function enrollClass(Request $request, Student $student): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $student->tenant_id !== $tenant->id || ! $user->hasBranchAccess($student->branch_id)) {
            abort(404, 'Siswa tidak ditemukan atau di luar cabang akses Anda.');
        }

        Gate::authorize('update', $student);

        $request->validate([
            'class_id' => ['required', 'uuid'],
            'started_at' => ['nullable', 'date'],
        ]);

        $class = Classes::where('tenant_id', $tenant->id)->findOrFail($request->input('class_id'));

        if (! $user->hasBranchAccess($class->branch_id)) {
            abort(403, 'Anda tidak memiliki akses ke cabang kelas yang dipilih.');
        }

        $enrollment = Enrollment::where('tenant_id', $tenant->id)
            ->where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->first();

        if ($enrollment) {
            $enrollment->update([
                'status' => 'active',
                'started_at' => $request->input('started_at', now()->toDateString()),
            ]);
        } else {
            Enrollment::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_id' => $class->branch_id,
                'student_id' => $student->id,
                'class_id' => $class->id,
                'started_at' => $request->input('started_at', now()->toDateString()),
                'status' => 'active',
            ]);
        }

        return redirect()->back()->with('success', "Siswa '{$student->name}' berhasil didaftarkan ke kelas {$class->name}.");
    }

    public function toggleEnrollment(Request $request, Student $student, Enrollment $enrollment): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $student->tenant_id !== $tenant->id || $enrollment->tenant_id !== $tenant->id || $enrollment->student_id !== $student->id || ! $user->hasBranchAccess($student->branch_id)) {
            abort(404, 'Enrollment tidak ditemukan.');
        }

        Gate::authorize('update', $student);

        $newStatus = $enrollment->status === 'active' ? 'withdrawn' : 'active';
        $enrollment->update([
            'status' => $newStatus,
            'ended_at' => $newStatus === 'withdrawn' ? now()->toDateString() : null,
        ]);

        $statusLabel = $newStatus === 'active' ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Status enrollment kelas berhasil {$statusLabel}.");
    }

    public function destroyEnrollment(Request $request, Student $student, Enrollment $enrollment): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant || $student->tenant_id !== $tenant->id || $enrollment->tenant_id !== $tenant->id || $enrollment->student_id !== $student->id || ! $user->hasBranchAccess($student->branch_id)) {
            abort(404, 'Enrollment tidak ditemukan.');
        }

        Gate::authorize('update', $student);

        $className = $enrollment->classModel?->name ?? 'Kelas';
        $enrollment->delete();

        return redirect()->back()->with('success', "Enrollment siswa dari {$className} berhasil dihapus.");
    }

    public function importTemplate(Request $request): Response
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('create', [Student::class, $user->accessibleBranchIds()->first()]);

        $csvContent = $this->importService->getTemplateCsv('students', $tenant);
        $filename = 'template_import_siswa_'.date('Ymd').'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function importPreview(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('create', [Student::class, $user->accessibleBranchIds()->first()]);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $rawRows = $this->importService->parseCsv($request->file('file'));

        if (empty($rawRows)) {
            return back()->with('error', 'File CSV kosong atau format header tidak terbaca. Pastikan menggunakan format template yang disediakan.');
        }

        $accessibleBranchIds = $user->accessibleBranchIds()->all();
        $result = $this->importService->previewStudents($tenant, $rawRows, $accessibleBranchIds);

        $validRows = $result['valid'];
        $errors = $result['errors'];

        $request->session()->put('admin_import_students_payload', $validRows);

        return view('admin.students.import-preview', compact('tenant', 'validRows', 'errors'));
    }

    public function importCommit(Request $request): RedirectResponse
    {
        $user = $request->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('create', [Student::class, $user->accessibleBranchIds()->first()]);

        $validRows = $request->session()->pull('admin_import_students_payload', []);

        if (empty($validRows)) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Sesi data import telah kedaluwarsa atau tidak ada data yang valid. Silakan unggah ulang file CSV.');
        }

        $accessibleBranchIds = $user->accessibleBranchIds()->all();
        // Double check branch authorization for each row
        $filteredRows = array_filter($validRows, function ($row) use ($accessibleBranchIds) {
            return in_array($row['branch_id'] ?? null, $accessibleBranchIds, true);
        });

        $count = $this->importService->commitStudents($tenant, $filteredRows);

        return redirect()->route('admin.students.index')
            ->with('success', "Berhasil mengimpor {$count} data siswa baru.");
    }
}
