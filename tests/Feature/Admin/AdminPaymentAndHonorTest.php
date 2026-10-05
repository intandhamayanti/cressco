<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\HonorCalculation;
use App\Models\HonorScheme;
use App\Models\Payment;
use App\Models\Student;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPaymentAndHonorTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Tenant $otherTenant;

    protected Branch $branchMalang;

    protected Branch $branchMakassar;

    protected Branch $branchOtherTenant;

    protected User $adminMalang;

    protected User $tutor;

    protected Student $studentMalang;

    protected Student $studentMakassar;

    protected Student $studentOtherTenant;

    protected Payment $paymentMalang;

    protected Payment $paymentMakassar;

    protected Payment $paymentOtherTenant;

    protected HonorScheme $scheme;

    protected HonorCalculation $honorMalang;

    protected HonorCalculation $honorMakassar;

    protected TeachingSession $sessionMalang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Tutoring Center',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Other Tutoring Center',
            'slug' => 'other-academy',
            'status' => 'active',
        ]);

        $this->branchMalang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Prime Academy - Malang',
            'code' => 'MLG-01',
            'status' => 'active',
        ]);

        $this->branchMakassar = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Prime Academy - Makassar',
            'code' => 'MKS-01',
            'status' => 'active',
        ]);

        $this->branchOtherTenant = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Other Branch Surabaya',
            'code' => 'SBY-01',
            'status' => 'active',
        ]);

        $this->adminMalang = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Budi Admin Malang',
            'email' => 'budi.admin@prime.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->adminMalang->id,
            'branch_id' => $this->branchMalang->id,
        ]);

        $this->tutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Dr. Bambang Tutor',
            'email' => 'bambang.tutor@prime.test',
            'password' => bcrypt('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->tutor->id,
            'branch_id' => $this->branchMalang->id,
        ]);

        $this->studentMalang = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Ahmad Siswa Malang',
            'parent_name' => 'Ibu Ahmad',
            'parent_phone' => '081234567890',
            'status' => 'active',
        ]);

        $this->studentMakassar = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Nurul Siswa Makassar',
            'parent_name' => 'Bapak Nurul',
            'parent_phone' => '089876543210',
            'status' => 'active',
        ]);

        $this->studentOtherTenant = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'name' => 'Citra Siswa Lain',
            'parent_name' => 'Ibu Citra',
            'parent_phone' => '085555555555',
            'status' => 'active',
        ]);

        $this->paymentMalang = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $this->studentMalang->id,
            'period' => 'Oktober 2026',
            'amount' => 500000,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'notes' => 'SPP Bulanan Reguler',
            'recorded_by' => $this->adminMalang->id,
        ]);

        $this->paymentMakassar = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'student_id' => $this->studentMakassar->id,
            'period' => 'Oktober 2026',
            'amount' => 750000,
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'menunggu_verifikasi',
            'notes' => 'SPP Makassar',
            'recorded_by' => $this->adminMalang->id,
        ]);

        $this->paymentOtherTenant = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'student_id' => $this->studentOtherTenant->id,
            'period' => 'Oktober 2026',
            'amount' => 600000,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'recorded_by' => $this->adminMalang->id,
        ]);

        $this->scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Honor Standar Malang',
            'method' => 'per_session',
            'rate' => 150000,
            'effective_from' => now()->startOfMonth(),
            'status' => 'active',
            'created_by' => $this->adminMalang->id,
        ]);

        $this->honorMalang = HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'tutor_id' => $this->tutor->id,
            'honor_scheme_id' => $this->scheme->id,
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'method' => 'per_session',
            'base_amount' => 450000,
            'adjustment_amount' => 0,
            'final_amount' => 450000,
            'status' => 'final',
            'calculated_by' => $this->adminMalang->id,
        ]);

        $this->honorMakassar = HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'tutor_id' => $this->tutor->id,
            'honor_scheme_id' => $this->scheme->id,
            'period_start' => now()->startOfMonth()->format('Y-m-d'),
            'period_end' => now()->endOfMonth()->format('Y-m-d'),
            'method' => 'per_session',
            'base_amount' => 600000,
            'adjustment_amount' => 0,
            'final_amount' => 600000,
            'status' => 'draft',
            'calculated_by' => $this->adminMalang->id,
        ]);

        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Fisika Intensif Malang',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $this->sessionMalang = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $class->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'actual_tutor_id' => $this->tutor->id,
            'session_date' => now()->format('Y-m-d'),
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'status' => 'completed',
            'material' => 'Hukum Termodinamika',
            'notes' => 'Sesi berlangsung lancar dan aktif.',
        ]);
    }

    public function test_guest_cannot_access_payments_or_honors(): void
    {
        $this->get(route('admin.payments.index'))->assertRedirect(route('login'));
        $this->get(route('admin.honors.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_admin_payments_or_honors(): void
    {
        $this->actingAs($this->tutor);
        $this->get(route('admin.payments.index'))->assertForbidden();
        $this->get(route('admin.honors.index'))->assertForbidden();
    }

    public function test_admin_can_list_payments_scoped_to_accessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->get(route('admin.payments.index'));

        $response->assertOk();
        $response->assertSee('Ahmad Siswa Malang');
        $response->assertDontSee('Nurul Siswa Makassar');
        $response->assertDontSee('Citra Siswa Lain');
    }

    public function test_cross_tenant_isolation_for_payments(): void
    {
        $this->actingAs($this->adminMalang);

        $this->get(route('admin.payments.show', $this->paymentOtherTenant))
            ->assertForbidden();
    }

    public function test_admin_can_search_and_filter_payments(): void
    {
        $this->actingAs($this->adminMalang);

        // Search by student name
        $response = $this->get(route('admin.payments.index', ['search' => 'Ahmad']));
        $response->assertOk();
        $response->assertSee('Ahmad Siswa Malang');

        $invoiceIdShort = '#'.substr($this->paymentMalang->id, 0, 8);

        // Search non-existent
        $response = $this->get(route('admin.payments.index', ['search' => 'NonExistentXYZ']));
        $response->assertOk();
        $response->assertDontSee($invoiceIdShort);

        // Filter by status
        $response = $this->get(route('admin.payments.index', ['status' => 'belum_bayar']));
        $response->assertOk();
        $response->assertSee($invoiceIdShort);

        $response = $this->get(route('admin.payments.index', ['status' => 'lunas']));
        $response->assertOk();
        $response->assertDontSee($invoiceIdShort);
    }

    public function test_admin_can_filter_payments_by_class_and_combined_filters_with_updated_ui(): void
    {
        $this->actingAs($this->adminMalang);

        // Create two classes in Malang branch
        $classA = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => '12 SMA Intensif UTBK',
            'status' => 'active',
        ]);

        $classB = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => '9 SMP Reguler',
            'status' => 'active',
        ]);

        // Enroll studentMalang in classA
        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $this->studentMalang->id,
            'class_id' => $classA->id,
            'started_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        // Create second student in Malang in classB
        $student2 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Budi Siswa SMP',
            'parent_name' => 'Pak Budi',
            'parent_phone' => '082233445566',
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $student2->id,
            'class_id' => $classB->id,
            'started_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        $paymentStudent2 = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $student2->id,
            'period' => 'Oktober 2026',
            'amount' => 350000,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'lunas',
            'notes' => 'SPP SMP Lunas',
            'recorded_by' => $this->adminMalang->id,
        ]);

        // 1. Visit index page without filter
        $response = $this->get(route('admin.payments.index'));
        $response->assertOk();
        $response->assertSee('12 SMA Intensif UTBK');
        $response->assertSee('9 SMP Reguler');
        $response->assertSee('Kirim Reminder');
        $response->assertDontSee('Buat Tagihan Baru');
        $response->assertDontSee('Semua Cabang Anda');

        // 2. Filter by Class A
        $responseClassA = $this->get(route('admin.payments.index', [
            'class_id' => $classA->id,
        ]));
        $responseClassA->assertOk();
        $responseClassA->assertSee('Ahmad Siswa Malang');
        $responseClassA->assertDontSee('Budi Siswa SMP');

        // 3. Combined filter: Class B + status lunas
        $responseCombined = $this->get(route('admin.payments.index', [
            'class_id' => $classB->id,
            'status' => 'lunas',
            'period' => 'Oktober 2026',
        ]));
        $responseCombined->assertOk();
        $responseCombined->assertSee('Budi Siswa SMP');
        $responseCombined->assertDontSee('Ahmad Siswa Malang');

        // 4. Combined filter: Class B + status belum_bayar (should show empty)
        $responseEmpty = $this->get(route('admin.payments.index', [
            'class_id' => $classB->id,
            'status' => 'belum_bayar',
        ]));
        $responseEmpty->assertOk();
        $responseEmpty->assertDontSee('Budi Siswa SMP');
        $responseEmpty->assertDontSee('Ahmad Siswa Malang');
    }

    public function test_admin_can_create_payment_in_accessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $postData = [
            'student_id' => $this->studentMalang->id,
            'branch_id' => $this->branchMalang->id,
            'period' => 'November 2026',
            'amount' => 550000,
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'notes' => 'Tagihan Bimbel Bulan November',
        ];

        $response = $this->post(route('admin.payments.store'), $postData);

        $response->assertRedirect(route('admin.payments.index'));
        $this->assertDatabaseHas('payments', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $this->studentMalang->id,
            'period' => 'November 2026',
            'amount' => 550000,
            'status' => 'belum_bayar',
        ]);
    }

    public function test_admin_cannot_create_payment_in_inaccessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $postData = [
            'student_id' => $this->studentMakassar->id,
            'branch_id' => $this->branchMakassar->id,
            'period' => 'November 2026',
            'amount' => 550000,
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'belum_bayar',
        ];

        $response = $this->post(route('admin.payments.store'), $postData);
        $response->assertSessionHasErrors('branch_id');
    }

    public function test_admin_can_view_payment_detail_with_parent_reminder_generation(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->get(route('admin.payments.show', $this->paymentMalang));

        $response->assertOk();
        $response->assertSee('Ahmad Siswa Malang');
        $response->assertSee('Ibu Ahmad');
        $response->assertSee('081234567890');
        $response->assertSee('Oktober 2026');
        $response->assertSee('Salin Pesan Reminder', false);
        $response->assertSee('https://wa.me/6281234567890', false);
    }

    public function test_admin_cannot_view_payment_in_inaccessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $this->get(route('admin.payments.show', $this->paymentMakassar))
            ->assertForbidden();
    }

    public function test_admin_can_update_payment_within_accessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $updateData = [
            'student_id' => $this->studentMalang->id,
            'branch_id' => $this->branchMalang->id,
            'period' => 'Oktober 2026 (Revisi)',
            'amount' => 600000,
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'notes' => 'Nominal disesuaikan dengan modul tambahan',
        ];

        $response = $this->put(route('admin.payments.update', $this->paymentMalang), $updateData);

        $response->assertRedirect(route('admin.payments.show', $this->paymentMalang));
        $this->assertDatabaseHas('payments', [
            'id' => $this->paymentMalang->id,
            'period' => 'Oktober 2026 (Revisi)',
            'amount' => 600000,
            'notes' => 'Nominal disesuaikan dengan modul tambahan',
        ]);
    }

    public function test_admin_can_verify_payment_as_lunas(): void
    {
        $this->actingAs($this->adminMalang);

        $verifyData = [
            'paid_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Telah ditransfer via BCA dan diverifikasi.',
        ];

        $response = $this->patch(route('admin.payments.verify', $this->paymentMalang), $verifyData);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', [
            'id' => $this->paymentMalang->id,
            'status' => 'lunas',
            'notes' => 'Telah ditransfer via BCA dan diverifikasi.',
        ]);
    }

    public function test_admin_cannot_verify_payment_in_inaccessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $this->patch(route('admin.payments.verify', $this->paymentMakassar), [])
            ->assertForbidden();
    }

    public function test_admin_can_delete_payment_in_accessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->delete(route('admin.payments.destroy', $this->paymentMalang));

        $response->assertRedirect(route('admin.payments.index'));
        $this->assertDatabaseMissing('payments', [
            'id' => $this->paymentMalang->id,
        ]);
    }

    public function test_admin_can_list_honor_calculations_in_branch_scope(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->get(route('admin.honors.index'));

        $response->assertOk();
        $response->assertSee('Dr. Bambang Tutor');
        $response->assertSee('Rekap Honor Tutor');
        $response->assertDontSee('Daftar Skema Kompensasi');
    }

    public function test_admin_can_view_honor_calculation_details_with_actual_teaching_sessions(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->get(route('admin.honors.show', $this->honorMalang));

        $response->assertOk();
        $response->assertSee('Dr. Bambang Tutor');
        $response->assertSee('Hukum Termodinamika');
        $response->assertSee('Fisika Intensif Malang');
        $response->assertSee('Rp 450.000', false);
    }

    public function test_admin_cannot_view_honor_in_inaccessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $this->get(route('admin.honors.show', $this->honorMakassar))
            ->assertForbidden();
    }

    public function test_admin_can_mark_honor_paid(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->patch(route('admin.honors.mark-paid', $this->honorMalang));

        $response->assertRedirect();
        $this->assertDatabaseHas('honor_calculations', [
            'id' => $this->honorMalang->id,
            'status' => 'paid',
        ]);
    }
}
