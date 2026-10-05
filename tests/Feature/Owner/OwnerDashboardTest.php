<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $owner;

    protected Branch $branchMalang;

    protected Branch $branchMakassar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Tutoring Center',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->owner = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Budi Pratama',
            'email' => 'owner@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'owner',
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
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/owner/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_non_owner_cannot_access_owner_dashboard(): void
    {
        $admin = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/owner/dashboard');
        $response->assertForbidden();
    }

    public function test_owner_can_view_dashboard_with_consolidated_tenant_data(): void
    {
        // Add student in Malang
        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Student Malang',
            'date_of_birth' => '2008-01-01',
            'gender' => 'Laki-laki',
            'joined_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        // Add student in Makassar
        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Student Makassar',
            'date_of_birth' => '2008-01-01',
            'gender' => 'Perempuan',
            'joined_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/dashboard');

        $response->assertOk();
        $response->assertViewIs('owner.dashboard');
        $response->assertSee('Prime Academy Tutoring Center');
        $response->assertSee('Prime Academy - Malang');
        $response->assertSee('Prime Academy - Makassar');
        $response->assertSee('Total Siswa Aktif');
        $response->assertSee('Revenue');
        $response->assertSee('Payment Overview');
        $response->assertSee('Revenue vs Expenses');
        $response->assertDontSee('AI Insight');
    }

    public function test_owner_dashboard_branch_selector_filters_data(): void
    {
        $studentMalang = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Siswa Malang',
            'date_of_birth' => '2008-01-01',
            'gender' => 'Laki-laki',
            'joined_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        $studentMakassar = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Siswa Makassar',
            'date_of_birth' => '2008-01-01',
            'gender' => 'Perempuan',
            'joined_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        // Add paid payment in Malang
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $studentMalang->id,
            'period' => now()->format('Y-m'),
            'amount' => 500000,
            'due_date' => now()->toDateString(),
            'paid_at' => now(),
            'status' => 'lunas',
            'recorded_by' => $this->owner->id,
        ]);

        // Add paid payment in Makassar
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'student_id' => $studentMakassar->id,
            'period' => now()->format('Y-m'),
            'amount' => 300000,
            'due_date' => now()->toDateString(),
            'paid_at' => now(),
            'status' => 'lunas',
            'recorded_by' => $this->owner->id,
        ]);

        // Request with branch filter for Malang
        $response = $this->actingAs($this->owner)->get('/owner/dashboard?branch_id='.$this->branchMalang->id);

        $response->assertOk();
        $response->assertSee('Menampilkan Data Cabang:');
        $response->assertSee('Prime Academy - Malang');
    }

    public function test_tenant_isolation_in_dashboard(): void
    {
        $otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bimbel Lain',
            'slug' => 'bimbel-lain',
            'status' => 'active',
        ]);

        $otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Cabang Lain',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/dashboard');
        $response->assertOk();
        $response->assertDontSee('Cabang Lain');
    }
}
