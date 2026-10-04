<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Student;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $adminMalang;

    protected User $adminMultiBranch;

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

        // Admin with access only to Malang
        $this->adminMalang = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Malang',
            'email' => 'admin.malang@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'user_id' => $this->adminMalang->id,
        ]);

        // Admin with access to both Malang and Makassar
        $this->adminMultiBranch = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Multi',
            'email' => 'admin.multi@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'user_id' => $this->adminMultiBranch->id,
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'user_id' => $this->adminMultiBranch->id,
        ]);

        $this->tutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor Prima',
            'email' => 'tutor.prima@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $tutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor User',
            'email' => 'tutor@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $response = $this->actingAs($tutor)->get('/admin/dashboard');
        $response->assertForbidden();
    }

    public function test_dashboard_redirects_admin_to_admin_dashboard(): void
    {
        $response = $this->actingAs($this->adminMalang)->get('/dashboard');
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_view_dashboard_with_operational_overview(): void
    {
        // 1. Create student in Malang
        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Siswa Malang A',
            'status' => 'active',
        ]);

        // 2. Create class in Malang
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Matematika 9A',
            'subject' => 'Matematika',
            'level' => 'SMP',
            'status' => 'active',
        ]);

        // 3. Create teaching session today
        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $class->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'scheduled',
            'room' => 'R-101',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas('activeStudentsCount', 1);
        $response->assertViewHas('activeClassesCount', 1);
        $response->assertViewHas('todaySessionsCount', 1);
        $response->assertSee('Matematika 9A');
        $response->assertSee('R-101');
    }

    public function test_admin_dashboard_is_strictly_scoped_to_admin_accessible_branches(): void
    {
        // Data in Malang (Admin Malang has access)
        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Siswa Malang',
            'status' => 'active',
        ]);

        $classMalang = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Fisika Malang',
            'status' => 'active',
        ]);

        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $classMalang->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '09:30',
            'status' => 'scheduled',
        ]);

        // Data in Makassar (Admin Malang does NOT have access)
        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Siswa Makassar',
            'status' => 'active',
        ]);

        $classMakassar = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Biologi Makassar',
            'status' => 'active',
        ]);

        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'class_id' => $classMakassar->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '13:00',
            'end_time' => '14:30',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('activeStudentsCount', 1);
        $response->assertViewHas('activeClassesCount', 1);
        $response->assertViewHas('todaySessionsCount', 1);
        $response->assertSee('Fisika Malang');
        $response->assertDontSee('Biologi Makassar');
    }

    public function test_admin_with_multiple_branches_can_filter_by_branch(): void
    {
        // Student in Malang
        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Siswa Malang',
            'status' => 'active',
        ]);

        // Student in Makassar
        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Siswa Makassar',
            'status' => 'active',
        ]);

        // When viewing all accessible branches
        $responseAll = $this->actingAs($this->adminMultiBranch)->get('/admin/dashboard');
        $responseAll->assertOk();
        $responseAll->assertViewHas('activeStudentsCount', 2);

        // When filtering by Malang
        $responseMalang = $this->actingAs($this->adminMultiBranch)->get('/admin/dashboard?branch_id='.$this->branchMalang->id);
        $responseMalang->assertOk();
        $responseMalang->assertViewHas('activeStudentsCount', 1);
        $responseMalang->assertViewHas('selectedBranchId', $this->branchMalang->id);
    }

    public function test_admin_cannot_filter_by_unauthorized_branch(): void
    {
        // Admin Malang tries to pass Makassar branch ID
        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Siswa Malang',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/dashboard?branch_id='.$this->branchMakassar->id);

        $response->assertOk();
        // Should fallback to Malang
        $response->assertViewHas('selectedBranchId', null);
        $response->assertViewHas('activeStudentsCount', 1);
    }

    public function test_tenant_isolation_on_admin_dashboard(): void
    {
        $otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Other Bimbel',
            'slug' => 'other-bimbel',
            'status' => 'active',
        ]);

        $otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Other Branch',
            'code' => 'OTH-01',
            'status' => 'active',
        ]);

        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'branch_id' => $otherBranch->id,
            'name' => 'Siswa Other Tenant',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('activeStudentsCount', 0);
    }
}
