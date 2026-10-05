<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminClassManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Tenant $otherTenant;

    protected Branch $branchMalang;

    protected Branch $branchMakassar;

    protected Branch $branchOtherTenant;

    protected User $adminMalang;

    protected User $tutor;

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
            'name' => 'Other Branch',
            'code' => 'OTH-01',
            'status' => 'active',
        ]);

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

        $this->tutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor Budi',
            'email' => 'budi.tutor@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);
    }

    public function test_guest_cannot_access_classes(): void
    {
        $response = $this->get('/admin/classes');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_classes_scoped_to_accessible_branch(): void
    {
        $classMalang = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Matematika Dasar Malang',
            'subject' => 'Matematika',
            'level' => 'SMA',
            'status' => 'active',
        ]);

        $classMakassar = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Fisika Makassar',
            'subject' => 'Fisika',
            'level' => 'SMA',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/classes');

        $response->assertOk();
        $response->assertSee('Matematika Dasar Malang');
        $response->assertDontSee('Fisika Makassar');
    }

    public function test_admin_can_search_and_filter_classes(): void
    {
        Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Biologi SMA Kelas 10',
            'subject' => 'Biologi',
            'status' => 'active',
        ]);

        Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Kimia SMA Kelas 12',
            'subject' => 'Kimia',
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/classes?search=Biologi');
        $response->assertOk();
        $response->assertSee('Biologi SMA Kelas 10');
        $response->assertDontSee('Kimia SMA Kelas 12');

        $responseStatus = $this->actingAs($this->adminMalang)->get('/admin/classes?status=inactive');
        $responseStatus->assertOk();
        $responseStatus->assertSee('Kimia SMA Kelas 12');
        $responseStatus->assertDontSee('Biologi SMA Kelas 10');
    }

    public function test_admin_can_create_class_in_accessible_branch(): void
    {
        $payload = [
            'branch_id' => $this->branchMalang->id,
            'name' => 'Bahasa Inggris TOEFL',
            'subject' => 'Bahasa Inggris',
            'level' => 'Umum',
            'capacity' => 20,
            'status' => 'active',
            'tutor_id' => $this->tutor->id,
        ];

        $response = $this->actingAs($this->adminMalang)->post('/admin/classes', $payload);

        $this->assertDatabaseHas('classes', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Bahasa Inggris TOEFL',
            'subject' => 'Bahasa Inggris',
            'capacity' => 20,
        ]);

        $createdClass = Classes::where('name', 'Bahasa Inggris TOEFL')->first();
        $this->assertNotNull($createdClass);

        $this->assertDatabaseHas('tutor_assignments', [
            'tenant_id' => $this->tenant->id,
            'class_id' => $createdClass->id,
            'tutor_id' => $this->tutor->id,
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.classes.show', $createdClass));
    }

    public function test_admin_cannot_create_class_in_inaccessible_branch(): void
    {
        $payload = [
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Kelas Ilegal Makassar',
            'subject' => 'Matematika',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->post('/admin/classes', $payload);

        $response->assertSessionHasErrors('branch_id');
        $this->assertDatabaseMissing('classes', [
            'name' => 'Kelas Ilegal Makassar',
        ]);
    }

    public function test_admin_can_view_class_details_in_accessible_branch(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Kelas Detail Test',
            'subject' => 'Fisika',
            'status' => 'active',
        ]);

        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Siswa Enrolled',
            'email' => 'siswa.enrolled@primeacademy.test',
            'nis' => 'NIS-888',
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

        $response = $this->actingAs($this->adminMalang)->get("/admin/classes/{$class->id}");

        $response->assertOk();
        $response->assertSee('Kelas Detail Test');
        $response->assertSee('Siswa Enrolled');
    }

    public function test_admin_cannot_view_class_in_inaccessible_branch(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Kelas Makassar Rahasia',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get("/admin/classes/{$class->id}");
        $response->assertNotFound();
    }

    public function test_admin_can_update_class(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Nama Lama',
            'subject' => 'Matematika',
            'status' => 'active',
        ]);

        $payload = [
            'branch_id' => $this->branchMalang->id,
            'name' => 'Nama Baru Diperbarui',
            'subject' => 'Matematika Lanjut',
            'level' => 'SMA Kelas 11',
            'capacity' => 25,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->put("/admin/classes/{$class->id}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('classes', [
            'id' => $class->id,
            'name' => 'Nama Baru Diperbarui',
            'subject' => 'Matematika Lanjut',
            'capacity' => 25,
        ]);
    }

    public function test_admin_can_toggle_class_status(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Kelas Toggle',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->patch("/admin/classes/{$class->id}/toggle-status");
        $response->assertRedirect();

        $this->assertEquals('inactive', $class->fresh()->status);

        $this->actingAs($this->adminMalang)->patch("/admin/classes/{$class->id}/toggle-status");
        $this->assertEquals('active', $class->fresh()->status);
    }

    public function test_admin_can_assign_tutor_and_toggle_assignment_status(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Kelas Penugasan Tutor',
            'status' => 'active',
        ]);

        $payload = [
            'tutor_id' => $this->tutor->id,
            'started_at' => now()->toDateString(),
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->post(route('admin.classes.assign-tutor', $class), $payload);
        $response->assertRedirect();

        $assignment = TutorAssignment::where('class_id', $class->id)->where('tutor_id', $this->tutor->id)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals('active', $assignment->status);

        // Toggle status
        $toggleResponse = $this->actingAs($this->adminMalang)->patch("/admin/classes/{$class->id}/tutors/{$assignment->id}/toggle-status");
        $toggleResponse->assertRedirect();
        $this->assertEquals('inactive', $assignment->fresh()->status);
    }

    public function test_cross_tenant_isolation_for_classes(): void
    {
        $otherClass = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'name' => 'Kelas Tenant Lain',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get("/admin/classes/{$otherClass->id}");
        $response->assertNotFound();
    }

    public function test_admin_can_batch_enroll_students_from_class_detail(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => '12 SMA - Biologi Intensif',
            'status' => 'active',
        ]);

        $student1 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Siswa Batch 1',
            'status' => 'active',
        ]);

        $student2 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Siswa Batch 2',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->post("/admin/classes/{$class->id}/enroll-students", [
            'student_ids' => [$student1->id, $student2->id],
            'started_at' => '2026-03-01',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $class->id,
            'student_id' => $student1->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('enrollments', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $class->id,
            'student_id' => $student2->id,
            'status' => 'active',
        ]);

        $this->assertEquals(2, $class->enrollments()->count());
    }

    public function test_admin_can_quick_create_student_from_class_detail_and_auto_enroll(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Kelas Quick Add',
            'status' => 'active',
        ]);

        $payload = [
            'name' => 'Siswa Baru Dari Kelas',
            'gender' => 'Perempuan',
            'phone' => '081299887766',
            'parent_name' => 'Ibu Rahma',
            'parent_phone' => '081299887767',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->post("/admin/classes/{$class->id}/students", $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student = Student::where('name', 'Siswa Baru Dari Kelas')->first();
        $this->assertNotNull($student);
        $this->assertEquals($this->branchMalang->id, $student->branch_id);

        $this->assertDatabaseHas('enrollments', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $class->id,
            'student_id' => $student->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_toggle_and_destroy_class_enrollment(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Kelas Test Enrollment Management',
            'status' => 'active',
        ]);

        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Siswa Test Enrollment',
            'status' => 'active',
        ]);

        $enrollment = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $class->id,
            'student_id' => $student->id,
            'status' => 'active',
            'started_at' => now()->toDateString(),
        ]);

        // Toggle status
        $response = $this->actingAs($this->adminMalang)->patch("/admin/classes/{$class->id}/enrollments/{$enrollment->id}/toggle");
        $response->assertRedirect();
        $this->assertEquals('withdrawn', $enrollment->fresh()->status);

        // Destroy enrollment
        $response = $this->actingAs($this->adminMalang)->delete("/admin/classes/{$class->id}/enrollments/{$enrollment->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);
    }
}
