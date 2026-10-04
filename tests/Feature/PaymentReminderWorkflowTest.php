<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PaymentReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentReminderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Tenant $otherTenant;

    protected Branch $branchSurabaya;

    protected Branch $branchMalang;

    protected Branch $branchOtherTenant;

    protected User $owner;

    protected User $adminSurabaya;

    protected User $tutor;

    protected Student $studentSurabaya;

    protected Student $studentMalang;

    protected Student $studentOtherTenant;

    protected PaymentReminderService $reminderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reminderService = app(PaymentReminderService::class);

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'EduCerdas Indonesia',
            'slug' => 'educerdas-indonesia',
            'status' => 'active',
        ]);

        $this->otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bimbel Lainnya',
            'slug' => 'bimbel-lainnya',
            'status' => 'active',
        ]);

        $this->branchSurabaya = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Surabaya Gubeng',
            'code' => 'SBY-01',
            'status' => 'active',
        ]);

        $this->branchMalang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Malang Ijen',
            'code' => 'MLG-01',
            'status' => 'active',
        ]);

        $this->branchOtherTenant = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Cabang Jakarta',
            'code' => 'JKT-01',
            'status' => 'active',
        ]);

        $this->owner = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Bambang Owner',
            'email' => 'bambang.owner@educerdas.test',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->adminSurabaya = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Siti Admin Surabaya',
            'email' => 'siti.admin@educerdas.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->adminSurabaya->id,
            'branch_id' => $this->branchSurabaya->id,
        ]);

        $this->tutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Agus Tutor',
            'email' => 'agus.tutor@educerdas.test',
            'password' => bcrypt('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->tutor->id,
            'branch_id' => $this->branchSurabaya->id,
        ]);

        $this->studentSurabaya = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'name' => 'Dimas Siswa Surabaya',
            'parent_name' => 'Bapak Dimas',
            'parent_phone' => '081234567890',
            'status' => 'active',
        ]);

        $this->studentMalang = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Nabila Siswa Malang',
            'parent_name' => 'Ibu Nabila',
            'parent_phone' => '085678901234',
            'status' => 'active',
        ]);

        $this->studentOtherTenant = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'name' => 'Siswa Luar',
            'parent_name' => 'Orang Tua Luar',
            'parent_phone' => '089999999999',
            'status' => 'active',
        ]);
    }

    public function test_reminder_generation_with_accurate_invoice_data(): void
    {
        $dueDate = now()->addDays(5)->format('Y-m-d');
        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'student_id' => $this->studentSurabaya->id,
            'period' => 'Oktober 2026',
            'amount' => 650000,
            'due_date' => $dueDate,
            'status' => 'belum_bayar',
            'recorded_by' => $this->adminSurabaya->id,
        ]);

        $reminder = $this->reminderService->generate($payment);

        $this->assertTrue($reminder['eligible']);
        $this->assertEquals('unpaid', $reminder['type']);
        $this->assertEquals('Dimas Siswa Surabaya', $reminder['student_name']);
        $this->assertEquals('Bapak Dimas', $reminder['parent_name']);
        $this->assertEquals('081234567890', $reminder['parent_phone']);
        $this->assertEquals('6281234567890', $reminder['wa_phone']);
        $this->assertEquals('Oktober 2026', $reminder['period']);
        $this->assertEquals(650000, $reminder['amount']);
        $this->assertEquals('Rp 650.000', $reminder['formatted_amount']);
        $this->assertStringContainsString('Bapak Dimas', $reminder['message']);
        $this->assertStringContainsString('Dimas Siswa Surabaya', $reminder['message']);
        $this->assertStringContainsString('Rp 650.000', $reminder['message']);
        $this->assertStringContainsString('Oktober 2026', $reminder['message']);
        $this->assertNotNull($reminder['wa_link']);
        $this->assertStringContainsString('https://wa.me/6281234567890', $reminder['wa_link']);
    }

    public function test_overdue_payment_reminder_generates_overdue_wording(): void
    {
        $pastDueDate = now()->subDays(3)->format('Y-m-d');
        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'student_id' => $this->studentSurabaya->id,
            'period' => 'September 2026',
            'amount' => 500000,
            'due_date' => $pastDueDate,
            'status' => 'terlambat',
            'recorded_by' => $this->adminSurabaya->id,
        ]);

        $reminder = $this->reminderService->generate($payment);

        $this->assertTrue($reminder['eligible']);
        $this->assertEquals('overdue', $reminder['type']);
        $this->assertStringContainsString('TERLAMBAT', $reminder['message']);
        $this->assertStringContainsString('telah melewati batas waktu jatuh tempo', $reminder['message']);
    }

    public function test_pending_verification_reminder_wording(): void
    {
        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'student_id' => $this->studentSurabaya->id,
            'period' => 'Oktober 2026',
            'amount' => 700000,
            'due_date' => now()->addDays(2)->format('Y-m-d'),
            'status' => 'menunggu_verifikasi',
            'recorded_by' => $this->adminSurabaya->id,
        ]);

        $reminder = $this->reminderService->generate($payment);

        $this->assertTrue($reminder['eligible']);
        $this->assertEquals('pending_verification', $reminder['type']);
        $this->assertStringContainsString('MENUNGGU VERIFIKASI', $reminder['message']);
        $this->assertStringContainsString('sedang dalam proses verifikasi', $reminder['message']);
    }

    public function test_paid_payment_is_not_eligible_for_collection_reminder(): void
    {
        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'student_id' => $this->studentSurabaya->id,
            'period' => 'Oktober 2026',
            'amount' => 700000,
            'due_date' => now()->addDays(2)->format('Y-m-d'),
            'paid_at' => now(),
            'status' => 'lunas',
            'recorded_by' => $this->adminSurabaya->id,
        ]);

        $this->assertFalse($this->reminderService->isEligible($payment));

        $reminder = $this->reminderService->generate($payment);
        $this->assertFalse($reminder['eligible']);
        $this->assertEquals('lunas', $reminder['type']);
        $this->assertStringContainsString('Terima kasih, pembayaran', $reminder['message']);
    }

    public function test_admin_can_fetch_reminder_json_in_accessible_branch(): void
    {
        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'student_id' => $this->studentSurabaya->id,
            'period' => 'November 2026',
            'amount' => 500000,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'recorded_by' => $this->adminSurabaya->id,
        ]);

        $this->actingAs($this->adminSurabaya);

        $response = $this->getJson(route('admin.payments.reminder', $payment));

        $response->assertOk();
        $response->assertJsonStructure([
            'eligible',
            'type',
            'student_name',
            'parent_name',
            'parent_phone',
            'wa_phone',
            'period',
            'amount',
            'formatted_amount',
            'due_date',
            'formatted_due_date',
            'status',
            'status_label',
            'message',
            'wa_link',
        ]);
        $response->assertJson([
            'student_name' => 'Dimas Siswa Surabaya',
            'period' => 'November 2026',
            'amount' => 500000,
            'status' => 'belum_bayar',
        ]);
    }

    public function test_admin_cannot_access_reminder_in_inaccessible_branch(): void
    {
        $paymentMalang = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $this->studentMalang->id,
            'period' => 'November 2026',
            'amount' => 500000,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'recorded_by' => $this->owner->id,
        ]);

        $this->actingAs($this->adminSurabaya);

        $this->getJson(route('admin.payments.reminder', $paymentMalang))->assertForbidden();
    }

    public function test_tenant_isolation_on_payment_reminder(): void
    {
        $paymentOther = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'student_id' => $this->studentOtherTenant->id,
            'period' => 'November 2026',
            'amount' => 500000,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'recorded_by' => $this->owner->id,
        ]);

        $this->actingAs($this->adminSurabaya);
        $this->getJson(route('admin.payments.reminder', $paymentOther))->assertForbidden();

        $this->actingAs($this->owner);
        $this->getJson(route('owner.payments.reminder', $paymentOther))->assertForbidden();
    }

    public function test_owner_can_access_reminder_across_all_branches(): void
    {
        $paymentMalang = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $this->studentMalang->id,
            'period' => 'Desember 2026',
            'amount' => 600000,
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'recorded_by' => $this->owner->id,
        ]);

        $this->actingAs($this->owner);

        $response = $this->getJson(route('owner.payments.reminder', $paymentMalang));
        $response->assertOk();
        $response->assertJson([
            'student_name' => 'Nabila Siswa Malang',
            'period' => 'Desember 2026',
        ]);
    }

    public function test_tutor_cannot_access_reminder_endpoints(): void
    {
        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'student_id' => $this->studentSurabaya->id,
            'period' => 'Desember 2026',
            'amount' => 500000,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'recorded_by' => $this->adminSurabaya->id,
        ]);

        $this->actingAs($this->tutor);

        $this->getJson(route('admin.payments.reminder', $payment))->assertForbidden();
        $this->getJson(route('owner.payments.reminder', $payment))->assertForbidden();
    }
}
