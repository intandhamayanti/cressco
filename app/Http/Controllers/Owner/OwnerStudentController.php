<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreStudentRequest;
use App\Http\Requests\Owner\UpdateStudentRequest;
use App\Models\Branch;
use App\Models\Student;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OwnerStudentController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        Gate::authorize('viewAny', Student::class);

        $search = $request->query('search');
        $branchId = $request->query('branch_id', 'all');
        $status = $request->query('status', 'all');

        $query = Student::where('tenant_id', $tenant->id)
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
            $query->where('branch_id', $branchId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $students = $query->orderBy('name')->get();

        // Metrics
        $totalStudentsCount = Student::where('tenant_id', $tenant->id)->count();
        $activeStudentsCount = Student::where('tenant_id', $tenant->id)->where('status', 'active')->count();
        $inactiveStudentsCount = $totalStudentsCount - $activeStudentsCount;

        $branches = Branch::where('tenant_id', $tenant->id)->orderBy('name')->get();

        return view('owner.students.index', compact(
            'tenant',
            'students',
            'branches',
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
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $student->tenant_id !== $tenant->id) {
            abort(404, 'Student not found in this tenant.');
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

        $branches = Branch::where('tenant_id', $tenant->id)->orderBy('name')->get();

        return view('owner.students.show', compact('tenant', 'student', 'branches'));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

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

        return redirect()
            ->route('owner.students.index')
            ->with('success', "Siswa '{$newStudent->name}' berhasil didaftarkan.");
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $student->tenant_id !== $tenant->id) {
            abort(404, 'Student not found in this tenant.');
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
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant || $student->tenant_id !== $tenant->id) {
            abort(404, 'Student not found in this tenant.');
        }

        Gate::authorize('update', $student);

        $newStatus = $student->status === 'active' ? 'inactive' : 'active';
        $student->update(['status' => $newStatus]);

        $statusLabel = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()
            ->back()
            ->with('success', "Status siswa '{$student->name}' berhasil {$statusLabel}.");
    }
}
