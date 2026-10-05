<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RecordPartialPaymentRequest;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Http\Requests\Admin\SubmitProofRequest;
use App\Http\Requests\Admin\UpdatePaymentRequest;
use App\Http\Requests\Admin\VerifyPaymentRequest;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Services\PaymentReminderService;
use App\Services\PaymentService;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentReminderService $reminderService
    ) {}

    public function index(Request $request): View
    {
        $admin = $request->user();
        $tenant = TenantContext::getTenant() ?? $admin->tenant;
        $tenantId = $tenant->id;

        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();
        $branches = Branch::where('tenant_id', $tenantId)
            ->whereIn('id', $accessibleBranchIds)
            ->orderBy('name')
            ->get();

        $classes = Classes::where('tenant_id', $tenantId)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $students = Student::where('tenant_id', $tenantId)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $enrollments = Enrollment::where('tenant_id', $tenantId)
            ->whereHas('classModel', function ($q) use ($accessibleBranchIds) {
                $q->whereIn('branch_id', $accessibleBranchIds);
            })
            ->with(['student', 'classModel'])
            ->where('status', 'active')
            ->get();

        $query = Payment::where('tenant_id', $tenantId)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->with(['student.enrollments.classModel', 'branch', 'enrollment.classModel', 'recordedBy']);

        if ($request->filled('branch_id')) {
            $selectedBranchId = $request->input('branch_id');
            if (! in_array($selectedBranchId, $accessibleBranchIds)) {
                abort(403, 'Anda tidak memiliki akses ke cabang ini.');
            }
            $query->where('branch_id', $selectedBranchId);
        }

        if ($request->filled('class_id') && $request->input('class_id') !== 'all') {
            $classId = $request->input('class_id');
            $query->where(function ($q) use ($classId) {
                $q->whereHas('enrollment', fn ($eq) => $eq->where('class_id', $classId))
                    ->orWhereHas('student.enrollments', fn ($sq) => $sq->where('class_id', $classId)->where('status', 'active'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('period')) {
            $query->where('period', $request->input('period'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('period', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('parent_name', 'like', "%{$search}%")
                            ->orWhere('parent_phone', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        // Summary calculations scoped to admin's branch access
        $summaryQuery = Payment::where('tenant_id', $tenantId)
            ->whereIn('branch_id', $accessibleBranchIds);

        if ($request->filled('branch_id')) {
            $summaryQuery->where('branch_id', $request->input('branch_id'));
        }
        if ($request->filled('class_id') && $request->input('class_id') !== 'all') {
            $classId = $request->input('class_id');
            $summaryQuery->where(function ($q) use ($classId) {
                $q->whereHas('enrollment', fn ($eq) => $eq->where('class_id', $classId))
                    ->orWhereHas('student.enrollments', fn ($sq) => $sq->where('class_id', $classId)->where('status', 'active'));
            });
        }
        if ($request->filled('period')) {
            $summaryQuery->where('period', $request->input('period'));
        }

        $totalInvoices = (clone $summaryQuery)->count();
        $totalRevenue = (clone $summaryQuery)->where('status', 'lunas')->sum('amount');
        $totalOutstanding = (clone $summaryQuery)->whereIn('status', ['belum_bayar', 'menunggu_verifikasi', 'terlambat'])->sum('amount');
        $totalOverdue = (clone $summaryQuery)->where('status', 'terlambat')->sum('amount');
        $totalPendingVerification = (clone $summaryQuery)->where('status', 'menunggu_verifikasi')->count();

        $periods = Payment::where('tenant_id', $tenantId)
            ->whereIn('branch_id', $accessibleBranchIds)
            ->distinct()
            ->orderBy('period', 'desc')
            ->pluck('period');

        $payments = $query->orderBy('due_date', 'desc')->paginate(15)->withQueryString();

        return view('admin.payments.index', [
            'tenant' => $tenant,
            'payments' => $payments,
            'branches' => $branches,
            'classes' => $classes,
            'students' => $students,
            'enrollments' => $enrollments,
            'periods' => $periods,
            'summary' => [
                'totalInvoices' => $totalInvoices,
                'totalRevenue' => $totalRevenue,
                'totalOutstanding' => $totalOutstanding,
                'totalOverdue' => $totalOverdue,
                'totalPendingVerification' => $totalPendingVerification,
            ],
            'filters' => [
                'branch_id' => $request->input('branch_id'),
                'class_id' => $request->input('class_id'),
                'status' => $request->input('status'),
                'period' => $request->input('period'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $admin = $request->user();
        $tenantId = TenantContext::getTenantId() ?? $admin->tenant_id;
        $data = $request->validated();
        $data['tenant_id'] = $tenantId;

        $proofFile = $request->file('proof') ?? $request->file('payment_proof');

        $this->paymentService->recordPayment($data, $admin, $proofFile);

        return redirect()->route('admin.payments.index')->with('success', 'Tagihan pembayaran berhasil dibuat.');
    }

    public function show(Request $request, Payment $payment): View
    {
        $admin = $request->user();
        $tenant = TenantContext::getTenant() ?? $admin->tenant;
        $tenantId = $tenant->id;

        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();
        abort_unless($payment->tenant_id === $tenantId && in_array($payment->branch_id, $accessibleBranchIds), 403, 'Akses ke tagihan pembayaran ditolak.');

        $payment->load(['student', 'branch', 'enrollment.classModel.subject', 'recordedBy']);

        $reminderData = $this->reminderService->generate($payment);

        $branches = Branch::where('tenant_id', $tenantId)->whereIn('id', $accessibleBranchIds)->orderBy('name')->get();
        $students = Student::where('tenant_id', $tenantId)->whereIn('branch_id', $accessibleBranchIds)->orderBy('name')->get();
        $enrollments = Enrollment::where('tenant_id', $tenantId)
            ->where('student_id', $payment->student_id)
            ->with('classModel')
            ->get();

        return view('admin.payments.show', [
            'tenant' => $tenant,
            'payment' => $payment,
            'branches' => $branches,
            'students' => $students,
            'enrollments' => $enrollments,
            'reminderData' => $reminderData,
            'reminderMessage' => $reminderData['message'],
            'waLink' => $reminderData['wa_link'],
            'isReminderEligible' => $reminderData['eligible'],
        ]);
    }

    public function reminder(Request $request, Payment $payment): JsonResponse
    {
        $admin = $request->user();
        $tenantId = TenantContext::getTenantId() ?? $admin->tenant_id;
        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();

        abort_unless($payment->tenant_id === $tenantId && in_array($payment->branch_id, $accessibleBranchIds), 403, 'Akses ke tagihan pembayaran ditolak.');

        $reminderData = $this->reminderService->generate($payment, $request->input('notes'));

        return response()->json($reminderData);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $admin = $request->user();
        $tenantId = TenantContext::getTenantId() ?? $admin->tenant_id;
        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();

        abort_unless($payment->tenant_id === $tenantId && in_array($payment->branch_id, $accessibleBranchIds), 403, 'Akses ke tagihan pembayaran ditolak.');

        $data = $request->validated();
        $proofFile = $request->file('proof') ?? $request->file('payment_proof');

        $this->paymentService->updatePayment($payment, $data, $admin, $proofFile);

        return redirect()->route('admin.payments.show', $payment)->with('success', 'Data tagihan/pembayaran berhasil diperbarui.');
    }

    public function verify(VerifyPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $admin = $request->user();
        $tenantId = TenantContext::getTenantId() ?? $admin->tenant_id;
        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();

        abort_unless($payment->tenant_id === $tenantId && in_array($payment->branch_id, $accessibleBranchIds), 403, 'Akses ke tagihan pembayaran ditolak.');

        $this->paymentService->verifyPayment(
            $payment,
            $admin,
            $request->input('paid_at'),
            $request->input('notes')
        );

        return redirect()->back()->with('success', 'Pembayaran berhasil diverifikasi dan status diubah menjadi Lunas.');
    }

    public function recordPartialPayment(RecordPartialPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $admin = $request->user();
        $tenantId = TenantContext::getTenantId() ?? $admin->tenant_id;
        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();

        abort_unless($payment->tenant_id === $tenantId && in_array($payment->branch_id, $accessibleBranchIds), 403, 'Akses ke tagihan pembayaran ditolak.');

        $proofFile = $request->file('proof') ?? $request->file('payment_proof');

        $result = $this->paymentService->recordPartialPayment(
            $payment,
            (float) $request->input('amount_paid'),
            $admin,
            $request->input('paid_at'),
            $request->input('notes'),
            $proofFile
        );

        $msg = $result['remaining_payment']
            ? 'Pembayaran parsial berhasil dicatat. Sisa tagihan telah dibuatkan invoice baru.'
            : 'Pembayaran penuh berhasil diverifikasi.';

        return redirect()->route('admin.payments.show', $result['paid_payment'])->with('success', $msg);
    }

    public function submitProof(SubmitProofRequest $request, Payment $payment): RedirectResponse
    {
        $admin = $request->user();
        $tenantId = TenantContext::getTenantId() ?? $admin->tenant_id;
        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();

        abort_unless($payment->tenant_id === $tenantId && in_array($payment->branch_id, $accessibleBranchIds), 403, 'Akses ke tagihan pembayaran ditolak.');

        $proofFile = $request->file('proof') ?? $request->file('payment_proof');

        $this->paymentService->submitProof($payment, $proofFile, $admin, $request->input('notes'));

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah. Status diubah menjadi Menunggu Verifikasi.');
    }

    public function destroy(Request $request, Payment $payment): RedirectResponse
    {
        $admin = $request->user();
        $tenantId = TenantContext::getTenantId() ?? $admin->tenant_id;
        $accessibleBranchIds = $admin->branches()->pluck('branches.id')->toArray();

        abort_unless($payment->tenant_id === $tenantId && in_array($payment->branch_id, $accessibleBranchIds), 403, 'Akses ke tagihan pembayaran ditolak.');

        $payment->delete();

        return redirect()->route('admin.payments.index')->with('success', 'Data pembayaran berhasil dihapus.');
    }
}
