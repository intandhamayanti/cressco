<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminTutorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Tenant $otherTenant;

    protected Branch $branchMalang;

    protected Branch $branchMakassar;

    protected Branch $branchOtherTenant;

    protected User $adminMalang;

    protected User $tutorMalang;

    protected Classes $classMalang;

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

        $this->tutorMalang = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor Budi Malang',
            'email' => 'budi.tutor@primeacademy.test',
            'phone' => '08123456789',
            'password' => bcrypt('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'user_id' => $this->tutorMalang->id,
        ]);

        $this->classMalang = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Fisika Dasar Malang',
            'subject' => 'Fisika',
            'status' => 'active',
        ]);
    }

    public function test_guest_cannot_access_tutors(): void
    {
        $response = $this->get('/admin/tutors');
        $response->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_admin_tutors(): void
    {
        $response = $this->actingAs($this->tutorMalang)->get('/admin/tutors');
        $response->assertForbidden();
    }

    public function test_admin_can_list_tutors_in_branch_scope(): void
    {
        $response = $this->actingAs($this->adminMalang)->get('/admin/tutors');

        $response->assertOk();
        $response->assertSee('Tutor Budi Malang');
        $response->assertSee('budi.tutor@primeacademy.test');
    }

    public function test_admin_cannot_see_tutors_from_other_tenants(): void
    {
        $otherTutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Tutor Alien',
            'email' => 'alien@other.test',
            'password' => bcrypt('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/tutors');

        $response->assertOk();
        $response->assertDontSee('Tutor Alien');
    }

    public function test_admin_can_search_and_filter_tutors(): void
    {
        $inactiveTutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor Siti Nonaktif',
            'email' => 'siti@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'tutor',
            'status' => 'inactive',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'user_id' => $inactiveTutor->id,
        ]);

        // Search by name
        $searchRes = $this->actingAs($this->adminMalang)->get('/admin/tutors?search=Budi');
        $searchRes->assertOk();
        $searchRes->assertSee('Tutor Budi Malang');
        $searchRes->assertDontSee('Tutor Siti Nonaktif');

        // Filter by status
        $statusRes = $this->actingAs($this->adminMalang)->get('/admin/tutors?status=inactive');
        $statusRes->assertOk();
        $statusRes->assertSee('Tutor Siti Nonaktif');
        $statusRes->assertDontSee('Tutor Budi Malang');
    }

    public function test_admin_can_create_tutor(): void
    {
        $payload = [
            'name' => 'Tutor Baru Handoko',
            'email' => 'handoko@primeacademy.test',
            'phone' => '081298765432',
            'status' => 'active',
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMalang->id,
        ];

        $response = $this->actingAs($this->adminMalang)->post('/admin/tutors', $payload);

        $this->assertDatabaseHas('users', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor Baru Handoko',
            'email' => 'handoko@primeacademy.test',
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $createdTutor = User::where('email', 'handoko@primeacademy.test')->first();
        $this->assertNotNull($createdTutor);

        // BranchUser check
        $this->assertDatabaseHas('branch_user', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'user_id' => $createdTutor->id,
        ]);

        // TutorAssignment check
        $this->assertDatabaseHas('tutor_assignments', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMalang->id,
            'tutor_id' => $createdTutor->id,
        ]);

        $response->assertRedirect(route('admin.tutors.show', $createdTutor));
    }

    public function test_admin_cannot_create_tutor_with_class_in_inaccessible_branch(): void
    {
        $classMakassar = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Fisika Makassar',
            'status' => 'active',
        ]);

        $payload = [
            'name' => 'Tutor Makassar Gagal',
            'email' => 'gagal@primeacademy.test',
            'class_id' => $classMakassar->id,
        ];

        $response = $this->actingAs($this->adminMalang)->post('/admin/tutors', $payload);

        $response->assertSessionHasErrors('class_id');
        $this->assertDatabaseMissing('users', ['email' => 'gagal@primeacademy.test']);
    }

    public function test_admin_can_view_tutor_detail(): void
    {
        // Add schedule and session
        Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMalang->id,
            'scheduled_tutor_id' => $this->tutorMalang->id,
            'day_of_week' => 1,
            'start_time' => '14:00',
            'end_time' => '15:30',
            'starts_on' => now()->toDateString(),
            'status' => 'active',
        ]);

        $session = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMalang->id,
            'scheduled_tutor_id' => $this->tutorMalang->id,
            'actual_tutor_id' => $this->tutorMalang->id,
            'session_date' => now()->toDateString(),
            'start_time' => '14:00',
            'end_time' => '15:30',
            'material' => 'Hukum Newton 1 & 2',
            'status' => 'completed',
        ]);

        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Siswa Murid',
            'email' => 'murid@primeacademy.test',
            'status' => 'active',
        ]);

        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $student->id,
            'teaching_session_id' => $session->id,
            'status' => 'hadir',
            'recorded_at' => now(),
            'recorded_by' => $this->tutorMalang->id,
        ]);

        $response = $this->actingAs($this->adminMalang)->get("/admin/tutors/{$this->tutorMalang->id}");

        $response->assertOk();
        $response->assertSee('Tutor Budi Malang');
        $response->assertSee('budi.tutor@primeacademy.test');
        $response->assertSee('Hukum Newton 1', false);
    }

    public function test_admin_can_update_tutor_profile(): void
    {
        $payload = [
            'name' => 'Tutor Budi Malang M.Pd',
            'email' => 'budi.mpd@primeacademy.test',
            'phone' => '089988776655',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->put("/admin/tutors/{$this->tutorMalang->id}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->tutorMalang->id,
            'name' => 'Tutor Budi Malang M.Pd',
            'email' => 'budi.mpd@primeacademy.test',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_toggle_tutor_status(): void
    {
        $response = $this->actingAs($this->adminMalang)->patch("/admin/tutors/{$this->tutorMalang->id}/toggle-status");
        $response->assertRedirect();
        $this->assertEquals('inactive', $this->tutorMalang->fresh()->status);

        $this->actingAs($this->adminMalang)->patch("/admin/tutors/{$this->tutorMalang->id}/toggle-status");
        $this->assertEquals('active', $this->tutorMalang->fresh()->status);
    }

    public function test_admin_can_assign_class_and_toggle_status(): void
    {
        $payload = [
            'class_id' => $this->classMalang->id,
            'started_at' => now()->toDateString(),
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->post("/admin/tutors/{$this->tutorMalang->id}/classes", $payload);
        $response->assertRedirect();

        $assignment = TutorAssignment::where('tutor_id', $this->tutorMalang->id)
            ->where('class_id', $this->classMalang->id)
            ->first();

        $this->assertNotNull($assignment);
        $this->assertEquals('active', $assignment->status);

        // Toggle assignment
        $toggleRes = $this->actingAs($this->adminMalang)->patch("/admin/tutors/{$this->tutorMalang->id}/classes/{$assignment->id}/toggle-status");
        $toggleRes->assertRedirect();
        $this->assertEquals('inactive', $assignment->fresh()->status);
    }

    public function test_cross_tenant_isolation_for_tutors(): void
    {
        $otherTutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Tutor Other Tenant',
            'email' => 'other.tutor@other.test',
            'password' => bcrypt('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->get("/admin/tutors/{$otherTutor->id}");
        $response->assertNotFound();

        $updateRes = $this->actingAs($this->adminMalang)->put("/admin/tutors/{$otherTutor->id}", [
            'name' => 'Hacked Name',
            'email' => 'other.tutor@other.test',
            'status' => 'active',
        ]);
        $updateRes->assertNotFound();
    }
}
