<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Tenant $otherTenant;

    protected Branch $branchJakarta;

    protected Branch $branchBandung;

    protected Branch $branchOtherTenant;

    protected User $owner;

    protected User $adminJakarta;

    protected User $tutor;

    protected Student $studentJakarta;

    protected Student $studentBandung;

    protected Student $studentOtherTenant;

    protected Classes $classJakarta;

    protected Enrollment $enrollmentJakarta;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->paymentService = app(PaymentService::class);

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bintang Prestasi Edu',
            'slug' => 'bintang-prestasi',
            'status' => 'active',
        ]);

        $this->otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Other Edu Center',
            'slug' => 'other-edu',
            'status' => 'active',
        ]);

        $this->branchJakarta = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Jakarta Pusat',
            'code' => 'JKT-01',
            'status' => 'active',
        ]);

        $this->branchBandung = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Bandung Dago',
            'code' => 'BDG-01',
            'status' => 'active',
        ]);

        $this->branchOtherTenant = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Cabang Surabaya',
            'code' => 'SBY-01',
            'status' => 'active',
        ]);

        $this->owner = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Hendro Owner',
            'email' => 'hendro.owner@bintang.test',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->adminJakarta = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Rina Admin Jakarta',
            'email' => 'rina.admin@bintang.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->adminJakarta->id,
            'branch_id' => $this->branchJakarta->id,
        ]);

        $this->tutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Pak Yudi Tutor',
            'email' => 'yudi.tutor@bintang.test',
            'password' => bcrypt('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->tutor->id,
            'branch_id' => $this->branchJakarta->id,
        ]);

        $this->studentJakarta = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'name' => 'Rian Siswa Jakarta',
            'parent_name' => 'Ibu Rian',
            'parent_phone' => '081122334455',
            'status' => 'active',
        ]);

        $this->studentBandung = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'name' => 'Dewi Siswa Bandung',
            'parent_name' => 'Bapak Dewi',
            'parent_phone' => '089988776655',
            'status' => 'active',
        ]);

        $this->studentOtherTenant = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'name' => 'Siswa Other Tenant',
            'parent_name' => 'Ibu Other',
            'parent_phone' => '087777777777',
            'status' => 'active',
        ]);

        $this->classJakarta = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'name' => 'Matematika Intensif 12 SMA',
            'capacity' => 15,
            'status' => 'active',
        ]);

        $this->enrollmentJakarta = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'student_id' => $this->studentJakarta->id,
            'class_id' => $this->classJakarta->id,
            'started_at' => now()->startOfMonth()->format('Y-m-d'),
            'status' => 'active',
        ]);
    }

    public function test_payment_creation_and_duplicate_prevention(): void
    {
        $this->actingAs($this->adminJakarta);

        $payload = [
            'student_id' => $this->studentJakarta->id,
            'branch_id' => $this->branchJakarta->id,
            'enrollment_id' => $this->enrollmentJakarta->id,
            'period' => 'Oktober 2026',
            'amount' => 750000,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'notes' => 'SPP Reguler Oktober 2026',
        ];

        $response = $this->post(route('admin.payments.store'), $payload);
        $response->assertRedirect(route('admin.payments.index'));

        $this->assertDatabaseHas('payments', [
            'tenant_id' => $this->tenant->id,
            'student_id' => $this->studentJakarta->id,
            'period' => 'Oktober 2026',
            'amount' => 750000,
            'status' => 'belum_bayar',
        ]);

        // Attempt duplicate creation for same student and period
        $dupResponse = $this->post(route('admin.payments.store'), $payload);
        $dupResponse->assertSessionHasErrors('period');
    }

    public function test_payment_creation_with_proof_auto_transitions_to_menunggu_verifikasi(): void
    {
        $this->actingAs($this->adminJakarta);

        $proofFile = UploadedFile::fake()->create('struk_transfer.jpg', 200, 'image/jpeg');

        $payload = [
            'student_id' => $this->studentJakarta->id,
            'branch_id' => $this->branchJakarta->id,
            'period' => 'November 2026',
            'amount' => 800000,
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'proof' => $proofFile,
        ];

        $response = $this->post(route('admin.payments.store'), $payload);
        $response->assertRedirect(route('admin.payments.index'));

        $payment = Payment::where('student_id', $this->studentJakarta->id)
            ->where('period', 'November 2026')
            ->firstOrFail();

        $this->assertEquals('menunggu_verifikasi', $payment->status);
        $this->assertStringContainsString('Bukti Pembayaran', $payment->notes);
    }

    public function test_payment_verification_workflow(): void
    {
        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'student_id' => $this->studentJakarta->id,
            'period' => 'Desember 2026',
            'amount' => 750000,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'menunggu_verifikasi',
            'recorded_by' => $this->adminJakarta->id,
        ]);

        $this->actingAs($this->adminJakarta);

        $verifyPayload = [
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Telah ditransfer via Mandiri dan diverifikasi.',
        ];

        $response = $this->patch(route('admin.payments.verify', $payment), $verifyPayload);
        $response->assertRedirect();

        $payment->refresh();
        $this->assertEquals('lunas', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('Telah ditransfer via Mandiri dan diverifikasi.', $payment->notes);

        // Attempt verifying again when already lunas should be rejected/validated
        $secondVerify = $this->patch(route('admin.payments.verify', $payment), $verifyPayload);
        $secondVerify->assertSessionHasErrors('status');
    }

    public function test_submitting_payment_proof_endpoint(): void
    {
        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'student_id' => $this->studentJakarta->id,
            'period' => 'Januari 2027',
            'amount' => 750000,
            'due_date' => now()->addDays(15)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'recorded_by' => $this->adminJakarta->id,
        ]);

        $this->actingAs($this->adminJakarta);

        $proofFile = UploadedFile::fake()->create('bukti_bayar.pdf', 300, 'application/pdf');

        $response = $this->post(route('admin.payments.submit-proof', $payment), [
            'proof' => $proofFile,
            'notes' => 'Diunggah bukti transfer ATM BCA',
        ]);

        $response->assertRedirect();

        $payment->refresh();
        $this->assertEquals('menunggu_verifikasi', $payment->status);
        $this->assertStringContainsString('Bukti Pembayaran diunggah oleh', $payment->notes);
        $this->assertStringContainsString('Diunggah bukti transfer ATM BCA', $payment->notes);
    }

    public function test_partial_payment_splits_and_preserves_financial_accuracy(): void
    {
        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'student_id' => $this->studentJakarta->id,
            'enrollment_id' => $this->enrollmentJakarta->id,
            'period' => 'Februari 2027',
            'amount' => 1000000,
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'recorded_by' => $this->adminJakarta->id,
        ]);

        $this->actingAs($this->adminJakarta);

        // Partial payment of 400,000 out of 1,000,000
        $response = $this->post(route('admin.payments.partial', $payment), [
            'amount_paid' => 400000,
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Cicilan termin 1 via QRIS',
        ]);

        $response->assertRedirect(route('admin.payments.show', $payment));

        // Original record is now 400,000 LUNAS
        $payment->refresh();
        $this->assertEquals('lunas', $payment->status);
        $this->assertEquals(400000, (float) $payment->amount);
        $this->assertNotNull($payment->paid_at);
        $this->assertStringContainsString('Cicilan termin 1 via QRIS', $payment->notes);

        // Remaining record is created for 600,000 BELUM BAYAR
        $remaining = Payment::where('tenant_id', $this->tenant->id)
            ->where('student_id', $this->studentJakarta->id)
            ->where('id', '!=', $payment->id)
            ->where('period', 'Februari 2027 (Sisa)')
            ->firstOrFail();

        $this->assertEquals(600000, (float) $remaining->amount);
        $this->assertEquals('belum_bayar', $remaining->status);

        // Revenue only counts verified/lunas (400,000), outstanding is 600,000
        $revenue = Payment::where('tenant_id', $this->tenant->id)->paid()->sum('amount');
        $outstanding = Payment::where('tenant_id', $this->tenant->id)->outstanding()->sum('amount');

        $this->assertEquals(400000, (float) $revenue);
        $this->assertEquals(600000, (float) $outstanding);
    }

    public function test_partial_payment_exceeding_total_amount_is_rejected(): void
    {
        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'student_id' => $this->studentJakarta->id,
            'period' => 'Maret 2027',
            'amount' => 500000,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'recorded_by' => $this->adminJakarta->id,
        ]);

        $this->actingAs($this->adminJakarta);

        $response = $this->post(route('admin.payments.partial', $payment), [
            'amount_paid' => 600000,
        ]);

        $response->assertSessionHasErrors('amount_paid');
    }

    public function test_overdue_sync_and_scope(): void
    {
        // Past due date payment
        $overduePayment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'student_id' => $this->studentJakarta->id,
            'period' => 'Agustus 2026',
            'amount' => 600000,
            'due_date' => now()->subDays(10)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'recorded_by' => $this->adminJakarta->id,
        ]);

        // Future due date payment
        $normalPayment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'student_id' => $this->studentJakarta->id,
            'period' => 'September 2026',
            'amount' => 600000,
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'recorded_by' => $this->adminJakarta->id,
        ]);

        // Trigger sync overdue
        $count = $this->paymentService->syncOverduePayments($this->tenant->id);
        $this->assertEquals(1, $count);

        $overduePayment->refresh();
        $normalPayment->refresh();

        $this->assertEquals('terlambat', $overduePayment->status);
        $this->assertEquals('belum_bayar', $normalPayment->status);
        $this->assertTrue($overduePayment->isOverdue());
    }

    public function test_revenue_and_outstanding_calculations(): void
    {
        // 1. Paid payment (Revenue: 500,000)
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'student_id' => $this->studentJakarta->id,
            'period' => 'P-1',
            'amount' => 500000,
            'due_date' => now()->toDateString(),
            'paid_at' => now(),
            'status' => 'lunas',
            'recorded_by' => $this->adminJakarta->id,
        ]);

        // 2. Unpaid payment (Outstanding: 300,000)
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'student_id' => $this->studentJakarta->id,
            'period' => 'P-2',
            'amount' => 300000,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'belum_bayar',
            'recorded_by' => $this->adminJakarta->id,
        ]);

        // 3. Pending verification payment (Outstanding: 200,000)
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'student_id' => $this->studentJakarta->id,
            'period' => 'P-3',
            'amount' => 200000,
            'due_date' => now()->addDays(2)->toDateString(),
            'status' => 'menunggu_verifikasi',
            'recorded_by' => $this->adminJakarta->id,
        ]);

        // 4. Overdue payment (Outstanding: 150,000, Overdue: 150,000)
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchJakarta->id,
            'student_id' => $this->studentJakarta->id,
            'period' => 'P-4',
            'amount' => 150000,
            'due_date' => now()->subDays(5)->toDateString(),
            'status' => 'terlambat',
            'recorded_by' => $this->adminJakarta->id,
        ]);

        $revenue = Payment::where('tenant_id', $this->tenant->id)->paid()->sum('amount');
        $outstanding = Payment::where('tenant_id', $this->tenant->id)->outstanding()->sum('amount');
        $overdue = Payment::where('tenant_id', $this->tenant->id)->where('status', 'terlambat')->sum('amount');

        $this->assertEquals(500000, (float) $revenue);
        $this->assertEquals(650000, (float) $outstanding); // 300,000 + 200,000 + 150,000
        $this->assertEquals(150000, (float) $overdue);
    }

    public function test_branch_scoping_and_tenant_isolation(): void
    {
        $paymentBandung = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'student_id' => $this->studentBandung->id,
            'period' => 'Oktober 2026',
            'amount' => 500000,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'belum_bayar',
            'recorded_by' => $this->owner->id,
        ]);

        $paymentOtherTenant = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'student_id' => $this->studentOtherTenant->id,
            'period' => 'Oktober 2026',
            'amount' => 500000,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'belum_bayar',
            'recorded_by' => $this->owner->id,
        ]);

        // Admin Jakarta cannot view or edit payment Bandung
        $this->actingAs($this->adminJakarta);

        $this->get(route('admin.payments.show', $paymentBandung))->assertForbidden();
        $this->patch(route('admin.payments.verify', $paymentBandung), [])->assertForbidden();
        $this->delete(route('admin.payments.destroy', $paymentBandung))->assertForbidden();

        // Admin Jakarta cannot access other tenant payment
        $this->get(route('admin.payments.show', $paymentOtherTenant))->assertForbidden();

        // Owner can access all branches in their tenant
        $this->actingAs($this->owner);
        $this->get(route('owner.payments.show', $paymentBandung))->assertOk();

        // Owner cannot access other tenant
        $this->get(route('owner.payments.show', $paymentOtherTenant))->assertForbidden();
    }

    public function test_tutor_is_strictly_forbidden_from_payment_endpoints(): void
    {
        $this->actingAs($this->tutor);

        $this->get(route('admin.payments.index'))->assertForbidden();
        $this->get(route('owner.payments.index'))->assertForbidden();
    }
}
