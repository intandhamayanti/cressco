<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Services\OwnerImportService;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OwnerImportController extends Controller
{
    public function __construct(
        protected OwnerImportService $importService
    ) {}

    public function create(Request $request, string $type): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        if ($type === 'students') {
            Gate::authorize('create', Student::class);
            $title = 'Import Data Siswa';
            $subtitle = 'Unggah data siswa dalam jumlah banyak menggunakan file spreadsheet CSV.';
        } else {
            Gate::authorize('create', User::class);
            $title = 'Import Data Tutor';
            $subtitle = 'Unggah data tutor dan skema honor awal dalam jumlah banyak menggunakan file CSV.';
        }

        return view('owner.imports.create', compact('tenant', 'type', 'title', 'subtitle'));
    }

    public function template(Request $request, string $type): Response
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        $csvContent = $this->importService->getTemplateCsv($type, $tenant);
        $filename = "template_import_{$type}_".date('Ymd').'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function preview(Request $request, string $type): View|RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // Max 5MB
        ]);

        $rawRows = $this->importService->parseCsv($request->file('file'));

        if (empty($rawRows)) {
            return back()->with('error', 'File CSV kosong atau format header tidak terbaca. Pastikan menggunakan format template yang disediakan.');
        }

        if ($type === 'students') {
            Gate::authorize('create', Student::class);
            $result = $this->importService->previewStudents($tenant, $rawRows);
            $title = 'Preview Validasi Import Siswa';
        } else {
            Gate::authorize('create', User::class);
            $result = $this->importService->previewTutors($tenant, $rawRows);
            $title = 'Preview Validasi Import Tutor';
        }

        $validRows = $result['valid'];
        $errors = $result['errors'];

        // Store valid rows in session for one-click commit
        $request->session()->put("import_{$type}_payload", $validRows);

        return view('owner.imports.preview', compact('tenant', 'type', 'title', 'validRows', 'errors'));
    }

    public function commit(Request $request, string $type): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        $validRows = $request->session()->pull("import_{$type}_payload", []);

        if (empty($validRows) && $request->has('rows')) {
            $rawRows = $request->input('rows');
            $fallbackBranchId = $request->input('branch_id');
            $validRows = array_map(function ($r) use ($fallbackBranchId) {
                if (empty($r['branch_id']) && $fallbackBranchId) {
                    $r['branch_id'] = $fallbackBranchId;
                }

                return $r;
            }, $rawRows);
        }

        if (empty($validRows)) {
            return redirect()->route('owner.imports.create', $type)
                ->with('error', 'Sesi data import telah kedaluwarsa atau tidak ada data yang valid. Silakan unggah ulang file CSV.');
        }

        if ($type === 'students') {
            Gate::authorize('create', Student::class);
            $count = $this->importService->commitStudents($tenant, $validRows);

            return redirect()->route('owner.students.index')
                ->with('success', "Berhasil mengimpor {$count} data siswa baru.");
        } else {
            Gate::authorize('create', User::class);
            $count = $this->importService->commitTutors($tenant, $validRows);

            return redirect()->route('owner.tutors.index')
                ->with('success', "Berhasil mengimpor {$count} data tutor baru.");
        }
    }
}
