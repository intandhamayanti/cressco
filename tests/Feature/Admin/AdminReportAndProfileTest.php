<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\HonorCalculation;
use App\Models\HonorScheme;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminReportAndProfileTest extends TestCase
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

    protected Payment $paymentMalang;

    protected HonorScheme $scheme;

    protected HonorCalculation $honorMalang;

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
            'password' => bcrypt('password123'),
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
            'password' => bcrypt('password123'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->studentMalang = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Ahmad Siswa Malang',
            'parent_name' => 'Ibu Ahmad',
            'status' => 'active',
        ]);

        $this->studentMakassar = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Nurul Siswa Makassar',
            'parent_name' => 'Bapak Nurul',
            'status' => 'active',
        ]);

        $this->paymentMalang = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $this->studentMalang->id,
            'period' => '2026-10',
            'amount' => 500000,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'lunas',
            'recorded_by' => $this->adminMalang->id,
        ]);

        $this->scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Standar Malang',
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
            'status' => 'paid',
            'calculated_by' => $this->adminMalang->id,
        ]);
    }

    public function test_guest_cannot_access_reports_or_profile(): void
    {
        $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
        $this->get(route('admin.profile'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_admin_reports_or_profile(): void
    {
        $this->actingAs($this->tutor);

        $this->get(route('admin.reports.index'))->assertForbidden();
        $this->get(route('admin.profile'))->assertForbidden();
    }

    public function test_admin_can_view_operational_reports_in_branch_scope(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->get(route('admin.reports.index'));

        $response->assertOk();
        $response->assertSee('Laporan Operasional Cabang');
        $response->assertSee('Ringkasan Siswa & Pendaftaran', false);
        $response->assertSee('Ringkasan Kelas & Jadwal', false);
        $response->assertSee('Ringkasan Presensi & Kehadiran', false);
        $response->assertSee('Ringkasan Tagihan & Kas', false);
        $response->assertSee('Ringkasan Honor Tutor Cabang', false);
    }

    public function test_admin_can_filter_reports_by_accessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->get(route('admin.reports.index', ['branch_id' => $this->branchMalang->id]));

        $response->assertOk();
    }

    public function test_admin_cannot_filter_reports_by_unauthorized_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $this->get(route('admin.reports.index', ['branch_id' => $this->branchMakassar->id]))
            ->assertForbidden();
    }

    public function test_admin_can_filter_reports_by_period(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->get(route('admin.reports.index', ['period' => '2026-10']));

        $response->assertOk();
    }

    public function test_admin_can_view_own_profile(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->get(route('admin.profile'));

        $response->assertOk();
        $response->assertSee('Budi Admin Malang');
        $response->assertSee('budi.admin@prime.test');
        $response->assertSee('Prime Academy - Malang');
    }

    public function test_admin_can_update_profile(): void
    {
        $this->actingAs($this->adminMalang);

        $updateData = [
            'name' => 'Budi Pratama Admin',
            'email' => 'budi.pratama@prime.test',
        ];

        $response = $this->put(route('admin.profile.update'), $updateData);

        $response->assertRedirect(route('admin.profile'));
        $this->assertDatabaseHas('users', [
            'id' => $this->adminMalang->id,
            'name' => 'Budi Pratama Admin',
            'email' => 'budi.pratama@prime.test',
        ]);
    }

    public function test_admin_cannot_update_profile_with_existing_email(): void
    {
        $this->actingAs($this->adminMalang);

        $updateData = [
            'name' => 'Budi Pratama Admin',
            'email' => 'bambang.tutor@prime.test', // tutor's email
        ];

        $response = $this->put(route('admin.profile.update'), $updateData);
        $response->assertSessionHasErrors('email');
    }

    public function test_admin_can_change_password(): void
    {
        $this->actingAs($this->adminMalang);

        $passwordData = [
            'current_password' => 'password123',
            'password' => 'newSecretPassword123',
            'password_confirmation' => 'newSecretPassword123',
        ];

        $response = $this->put(route('admin.profile.password'), $passwordData);

        $response->assertRedirect(route('admin.profile'));
        $this->adminMalang->refresh();
        $this->assertTrue(Hash::check('newSecretPassword123', $this->adminMalang->password));
    }

    public function test_admin_cannot_change_password_with_wrong_current_password(): void
    {
        $this->actingAs($this->adminMalang);

        $passwordData = [
            'current_password' => 'wrongCurrentPassword',
            'password' => 'newSecretPassword123',
            'password_confirmation' => 'newSecretPassword123',
        ];

        $response = $this->put(route('admin.profile.password'), $passwordData);
        $response->assertSessionHasErrors('current_password');
    }

    public function test_admin_can_logout(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
