<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerStudentManagementTest extends TestCase
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

    public function test_owner_can_list_students(): void
    {
        $student1 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Rizky Siswa',
            'phone' => '08123456789',
            'status' => 'active',
        ]);

        $student2 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Siti Siswi',
            'phone' => '08129876543',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/students');

        $response->assertOk();
        $response->assertViewIs('owner.students.index');
        $response->assertSee('Rizky Siswa');
        $response->assertSee('Siti Siswi');
    }

    public function test_owner_cannot_see_other_tenant_students(): void
    {
        $otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Lain',
            'slug' => 'tenant-lain',
            'status' => 'active',
        ]);

        $otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Cabang Lain',
            'status' => 'active',
        ]);

        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'branch_id' => $otherBranch->id,
            'name' => 'Siswa Rahasia Lain',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/students');

        $response->assertOk();
        $response->assertDontSee('Siswa Rahasia Lain');
    }

    public function test_owner_can_create_student(): void
    {
        $response = $this->actingAs($this->owner)->post('/owner/students', [
            'name' => 'Ananda Pratama',
            'branch_id' => $this->branchMalang->id,
            'date_of_birth' => '2008-04-12',
            'gender' => 'Laki-laki',
            'phone' => '081234567890',
            'address' => 'Jl. Kawi No. 10, Malang',
            'parent_name' => 'Bambang Pratama',
            'parent_phone' => '081299887766',
            'notes' => 'Persiapan UTBK SNBT',
            'status' => 'active',
        ]);

        $response->assertRedirect('/owner/students');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Ananda Pratama',
            'phone' => '081234567890',
            'status' => 'active',
        ]);
    }

    public function test_student_creation_rejects_non_tenant_branch(): void
    {
        $otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Lain',
            'slug' => 'tenant-lain',
            'status' => 'active',
        ]);

        $otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Cabang Lain',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->post('/owner/students', [
            'name' => 'Hacker Student',
            'branch_id' => $otherBranch->id,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors(['branch_id']);
        $this->assertDatabaseMissing('students', ['name' => 'Hacker Student']);
    }

    public function test_owner_can_view_student_details(): void
    {
        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Budi Siswa Detail',
            'phone' => '081333444555',
            'parent_name' => 'Joko Orang Tua',
            'status' => 'active',
        ]);

        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => '12 IPA - Matematika',
            'subject' => 'Matematika',
            'level' => '12 SMA',
            'capacity' => 20,
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $student->id,
            'class_id' => $class->id,
            'started_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get("/owner/students/{$student->id}");

        $response->assertOk();
        $response->assertViewIs('owner.students.show');
        $response->assertSee('Budi Siswa Detail');
        $response->assertSee('Joko Orang Tua');
        $response->assertSee('12 IPA - Matematika');
    }

    public function test_owner_cannot_view_other_tenant_student_details(): void
    {
        $otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Lain',
            'slug' => 'tenant-lain',
            'status' => 'active',
        ]);

        $otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Cabang Lain',
            'status' => 'active',
        ]);

        $otherStudent = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'branch_id' => $otherBranch->id,
            'name' => 'Other Student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get("/owner/students/{$otherStudent->id}");
        $response->assertNotFound();
    }

    public function test_owner_can_update_student(): void
    {
        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Nama Awal',
            'phone' => '0811111111',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->put("/owner/students/{$student->id}", [
            'name' => 'Nama Diperbarui',
            'branch_id' => $this->branchMakassar->id,
            'phone' => '0822222222',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('Nama Diperbarui', $student->name);
        $this->assertEquals($this->branchMakassar->id, $student->branch_id);
        $this->assertEquals('0822222222', $student->phone);
    }

    public function test_owner_can_toggle_student_status(): void
    {
        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Siswa Toggle',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->patch("/owner/students/{$student->id}/toggle-status");

        $response->assertRedirect();
        $student->refresh();
        $this->assertEquals('inactive', $student->status);

        // Toggle back
        $response2 = $this->actingAs($this->owner)->patch("/owner/students/{$student->id}/toggle-status");
        $response2->assertRedirect();
        $student->refresh();
        $this->assertEquals('active', $student->status);
    }

    public function test_student_search_and_branch_filtering(): void
    {
        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Ahmad Dahlan',
            'phone' => '081234567890',
            'status' => 'active',
        ]);

        Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Hasanuddin',
            'phone' => '081987654321',
            'status' => 'inactive',
        ]);

        // Search
        $resSearch = $this->actingAs($this->owner)->get('/owner/students?search=Dahlan');
        $resSearch->assertOk();
        $resSearch->assertSee('Ahmad Dahlan');
        $resSearch->assertDontSee('Hasanuddin');

        // Branch filter
        $resBranch = $this->actingAs($this->owner)->get('/owner/students?branch_id='.$this->branchMakassar->id);
        $resBranch->assertOk();
        $resBranch->assertSee('Hasanuddin');
        $resBranch->assertDontSee('Ahmad Dahlan');

        // Status filter
        $resStatus = $this->actingAs($this->owner)->get('/owner/students?status=inactive');
        $resStatus->assertOk();
        $resStatus->assertSee('Hasanuddin');
        $resStatus->assertDontSee('Ahmad Dahlan');
    }
}
