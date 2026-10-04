<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\RecordPartialPaymentRequest;
use App\Http\Requests\Owner\StorePaymentRequest;
use App\Http\Requests\Owner\SubmitProofRequest;
use App\Http\Requests\Owner\UpdatePaymentRequest;
use App\Http\Requests\Owner\VerifyPaymentRequest;
use App\Models\Branch;
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

class OwnerPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentReminderService $reminderService
    ) {}

    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;
        $tenantId = $tenant->id;

        $branches = Branch::where('tenant_id', $tenantId)->orderBy('name')->get();
        $students = Student::where('tenant_id', $tenantId)->where('status', 'active')->orderBy('name')->get();
        $enrollments = Enrollment::where('tenant_id', $tenantId)
            ->with(['student', 'classModel'])
            ->where('status', 'active')
            ->get();

        $query = Payment::where('tenant_id', $tenantId)
            ->with(['student', 'branch', 'enrollment.classModel', 'recordedBy']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
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

        $summaryQuery = Payment::where('tenant_id', $tenantId);
        if ($request->filled('branch_id')) {
            $summaryQuery->where('branch_id', $request->input('branch_id'));
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
            ->distinct()
            ->orderBy('period', 'desc')
            ->pluck('period');

        $payments = $query->orderBy('due_date', 'desc')->paginate(15)->withQueryString();

        return view('owner.payments.index', [
            'tenant' => $tenant,
            'payments' => $payments,
            'branches' => $branches,
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
                'status' => $request->input('status'),
                'period' => $request->input('period'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $tenantId = TenantContext::getTenantId() ?? $request->user()->tenant_id;
        $data = $request->validated();
        $data['tenant_id'] = $tenantId;

        $proofFile = $request->file('proof') ?? $request->file('payment_proof');

        $this->paymentService->recordPayment($data, $request->user(), $proofFile);

        return redirect()->route('owner.payments.index')->with('success', 'Tagihan pembayaran berhasil dibuat.');
    }

    public function show(Payment $payment): View
    {
        $tenant = TenantContext::getTenant() ?? auth()->user()->tenant;
        $tenantId = $tenant->id;
        abort_unless($payment->tenant_id === $tenantId, 403);

        $payment->load(['student', 'branch', 'enrollment.classModel.subject', 'recordedBy']);

        $reminderData = $this->reminderService->generate($payment);

        $branches = Branch::where('tenant_id', $tenantId)->orderBy('name')->get();
        $students = Student::where('tenant_id', $tenantId)->orderBy('name')->get();
        $enrollments = Enrollment::where('tenant_id', $tenantId)
            ->where('student_id', $payment->student_id)
            ->with('classModel')
            ->get();

        return view('owner.payments.show', [
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
        $tenantId = TenantContext::getTenantId() ?? auth()->user()->tenant_id;
        abort_unless($payment->tenant_id === $tenantId, 403);

        $reminderData = $this->reminderService->generate($payment, $request->input('notes'));

        return response()->json($reminderData);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $tenantId = TenantContext::getTenantId() ?? $request->user()->tenant_id;
        abort_unless($payment->tenant_id === $tenantId, 403);

        $data = $request->validated();
        $proofFile = $request->file('proof') ?? $request->file('payment_proof');

        $this->paymentService->updatePayment($payment, $data, $request->user(), $proofFile);

        return redirect()->route('owner.payments.show', $payment)->with('success', 'Data tagihan/pembayaran berhasil diperbarui.');
    }

    public function verify(VerifyPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $tenantId = TenantContext::getTenantId() ?? $request->user()->tenant_id;
        abort_unless($payment->tenant_id === $tenantId, 403);

        $this->paymentService->verifyPayment(
            $payment,
            $request->user(),
            $request->input('paid_at'),
            $request->input('notes')
        );

        return redirect()->back()->with('success', 'Pembayaran berhasil diverifikasi dan status diubah menjadi Lunas.');
    }

    public function recordPartialPayment(RecordPartialPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $tenantId = TenantContext::getTenantId() ?? $request->user()->tenant_id;
        abort_unless($payment->tenant_id === $tenantId, 403);

        $proofFile = $request->file('proof') ?? $request->file('payment_proof');

        $result = $this->paymentService->recordPartialPayment(
            $payment,
            (float) $request->input('amount_paid'),
            $request->user(),
            $request->input('paid_at'),
            $request->input('notes'),
            $proofFile
        );

        $msg = $result['remaining_payment']
            ? 'Pembayaran parsial berhasil dicatat. Sisa tagihan telah dibuatkan invoice baru.'
            : 'Pembayaran penuh berhasil diverifikasi.';

        return redirect()->route('owner.payments.show', $result['paid_payment'])->with('success', $msg);
    }

    public function submitProof(SubmitProofRequest $request, Payment $payment): RedirectResponse
    {
        $tenantId = TenantContext::getTenantId() ?? $request->user()->tenant_id;
        abort_unless($payment->tenant_id === $tenantId, 403);

        $proofFile = $request->file('proof') ?? $request->file('payment_proof');

        $this->paymentService->submitProof($payment, $proofFile, $request->user(), $request->input('notes'));

        return redirect()->back()->with('success', 'Bukti pembayaran berhasil diunggah. Status diubah menjadi Menunggu Verifikasi.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $tenantId = TenantContext::getTenantId() ?? auth()->user()->tenant_id;
        abort_unless($payment->tenant_id === $tenantId, 403);

        $payment->delete();

        return redirect()->route('owner.payments.index')->with('success', 'Data pembayaran berhasil dihapus.');
    }
}
