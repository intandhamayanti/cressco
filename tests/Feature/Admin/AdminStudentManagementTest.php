<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminStudentManagementTest extends TestCase
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

        // Admin with access to Malang & Makassar
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
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/students');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_student_management(): void
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

        $response = $this->actingAs($tutor)->get('/admin/students');
        $response->assertForbidden();
    }

    public function test_admin_can_list_students_within_accessible_branches(): void
    {
        $studentMalang = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Rizky Malang',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $studentMakassar = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Siti Makassar',
            'phone' => '08129876543',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/students');

        $response->assertOk();
        $response->assertViewIs('admin.students.index');
        $response->assertSee('Rizky Malang');
        $response->assertDontSee('Siti Makassar');
    }

    public function test_admin_cannot_see_students_from_other_tenants(): void
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
            'name' => 'Foreign Student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/students');

        $response->assertOk();
        $response->assertDontSee('Foreign Student');
    }

    public function test_admin_can_search_students(): void
    {
        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Ahmad Dani',
            'phone' => '08111111111',
            'status' => 'active',
        ]);

        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Bambang Sudirman',
            'phone' => '08222222222',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/students?search=Ahmad');

        $response->assertOk();
        $response->assertSee('Ahmad Dani');
        $response->assertDontSee('Bambang Sudirman');
    }

    public function test_admin_can_filter_students_by_branch_and_status(): void
    {
        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Active Student',
            'status' => 'active',
        ]);

        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Inactive Student',
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/students?status=active');

        $response->assertOk();
        $response->assertSee('Active Student');
        $response->assertDontSee('Inactive Student');
    }

    public function test_admin_can_create_student_in_accessible_branch(): void
    {
        $payload = [
            'name' => 'Dewi Lestari',
            'branch_id' => $this->branchMalang->id,
            'date_of_birth' => '2010-05-15',
            'gender' => 'Perempuan',
            'phone' => '081234567800',
            'address' => 'Jl. Ijen No. 12, Malang',
            'parent_name' => 'Bapak Lestari',
            'parent_phone' => '081234567801',
            'notes' => 'Minat di olimpiade fisika',
            'joined_at' => '2026-02-01',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->post('/admin/students', $payload);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Dewi Lestari',
            'gender' => 'Perempuan',
            'status' => 'active',
        ]);
    }

    public function test_admin_cannot_create_student_in_unauthorized_branch(): void
    {
        // Admin Malang tries to create student in Makassar
        $payload = [
            'name' => 'Unauthorized Student',
            'branch_id' => $this->branchMakassar->id,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->post('/admin/students', $payload);

        $response->assertSessionHasErrors(['branch_id']);
        $this->assertDatabaseMissing('students', [
            'name' => 'Unauthorized Student',
        ]);
    }

    public function test_admin_cannot_create_student_without_required_fields(): void
    {
        $response = $this->actingAs($this->adminMalang)->post('/admin/students', []);

        $response->assertSessionHasErrors(['name', 'branch_id']);
    }

    public function test_admin_can_view_student_details_within_accessible_branch(): void
    {
        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Fajar Pratama',
            'phone' => '081333444555',
            'parent_name' => 'Pak Joko',
            'parent_phone' => '081333444556',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/students/'.$student->id);

        $response->assertOk();
        $response->assertViewIs('admin.students.show');
        $response->assertSee('Fajar Pratama');
        $response->assertSee('Pak Joko');
    }

    public function test_admin_cannot_view_student_details_from_unauthorized_branch(): void
    {
        $studentMakassar = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Makassar Student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/students/'.$studentMakassar->id);

        $response->assertNotFound();
    }

    public function test_admin_can_update_student_within_accessible_branch(): void
    {
        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Old Name',
            'status' => 'active',
        ]);

        $payload = [
            'name' => 'New Updated Name',
            'branch_id' => $this->branchMalang->id,
            'gender' => 'Laki-laki',
            'phone' => '081999888777',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->put('/admin/students/'.$student->id, $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'name' => 'New Updated Name',
            'phone' => '081999888777',
        ]);
    }

    public function test_admin_cannot_move_student_to_unauthorized_branch(): void
    {
        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Malang Student',
            'status' => 'active',
        ]);

        // Admin Malang tries to change branch to Makassar
        $payload = [
            'name' => 'Malang Student',
            'branch_id' => $this->branchMakassar->id,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->put('/admin/students/'.$student->id, $payload);

        $response->assertSessionHasErrors(['branch_id']);
        $this->assertEquals($this->branchMalang->id, $student->fresh()->branch_id);
    }

    public function test_admin_can_toggle_student_status_within_accessible_branch(): void
    {
        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Student To Toggle',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->patch('/admin/students/'.$student->id.'/toggle-status');

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('inactive', $student->fresh()->status);

        // Toggle back to active
        $this->actingAs($this->adminMalang)->patch('/admin/students/'.$student->id.'/toggle-status');
        $this->assertEquals('active', $student->fresh()->status);
    }

    public function test_admin_cannot_toggle_student_status_for_unauthorized_branch(): void
    {
        $studentMakassar = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Makassar Student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->patch('/admin/students/'.$studentMakassar->id.'/toggle-status');

        $response->assertNotFound();
        $this->assertEquals('active', $studentMakassar->fresh()->status);
    }
}
