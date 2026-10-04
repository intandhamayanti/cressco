<?php

namespace Tests\Feature\Tutor;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TutorDashboardAndScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Tenant $otherTenant;

    protected Branch $branchMalang;

    protected Branch $branchSurabaya;

    protected User $tutorA;

    protected User $tutorB;

    protected Classes $classMath;

    protected Classes $classPhysics;

    protected Classes $classOtherTenant;

    protected Schedule $scheduleMathMonday;

    protected Schedule $schedulePhysicsTuesday;

    protected Student $studentJohn;

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
            'name' => 'Apex Learning Center',
            'slug' => 'apex-learning',
            'status' => 'active',
        ]);

        $this->branchMalang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Prime Academy - Malang',
            'code' => 'MLG-01',
            'status' => 'active',
        ]);

        $this->branchSurabaya = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Prime Academy - Surabaya',
            'code' => 'SBY-01',
            'status' => 'active',
        ]);

        $otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Apex - Jakarta',
            'code' => 'JKT-01',
            'status' => 'active',
        ]);

        // Tutor A (Primary test tutor)
        $this->tutorA = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor Arya Wijaya',
            'email' => 'arya.tutor@primeacademy.test',
            'role' => 'tutor',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
            'email_verified_at' => now(),
        ]);

        // Tutor B (Another tutor in same tenant)
        $this->tutorB = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor Bella Safitri',
            'email' => 'bella.tutor@primeacademy.test',
            'role' => 'tutor',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
            'email_verified_at' => now(),
        ]);

        // Class 1 (Assigned to Tutor A)
        $this->classMath = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Matematika SMA Kelas 12',
            'subject' => 'Matematika',
            'level' => 'SMA',
            'capacity' => 20,
            'status' => 'active',
        ]);

        // Assign Tutor A to Class 1
        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'tutor_id' => $this->tutorA->id,
            'class_id' => $this->classMath->id,
            'started_at' => now()->subMonth()->toDateString(),
            'status' => 'active',
        ]);

        // Class 2 (Assigned ONLY to Tutor B)
        $this->classPhysics = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'name' => 'Fisika Dasar Kelas 10',
            'subject' => 'Fisika',
            'level' => 'SMA',
            'capacity' => 15,
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'tutor_id' => $this->tutorB->id,
            'class_id' => $this->classPhysics->id,
            'started_at' => now()->subMonth()->toDateString(),
            'status' => 'active',
        ]);

        // Class in Other Tenant
        $this->classOtherTenant = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $otherBranch->id,
            'name' => 'Kimia SBMPTN',
            'subject' => 'Kimia',
            'level' => 'SMA',
            'capacity' => 10,
            'status' => 'active',
        ]);

        // Schedules
        $this->scheduleMathMonday = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 1, // Senin
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang 101',
            'starts_on' => now()->startOfYear()->toDateString(),
            'status' => 'active',
        ]);

        $this->schedulePhysicsTuesday = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'class_id' => $this->classPhysics->id,
            'scheduled_tutor_id' => $this->tutorB->id,
            'day_of_week' => 2, // Selasa
            'start_time' => '18:00:00',
            'end_time' => '19:30:00',
            'room' => 'Lab Fisika',
            'starts_on' => now()->startOfYear()->toDateString(),
            'status' => 'active',
        ]);

        // Student Enrollment in Math Class
        $this->studentJohn = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Johnathan Doe',
            'nis' => 'PA-MLG-2026-001',
            'grade' => '12 SMA',
            'parent_name' => 'Robert Doe',
            'parent_phone' => '081234567890',
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $this->studentJohn->id,
            'class_id' => $this->classMath->id,
            'started_at' => now()->subMonth()->toDateString(),
            'status' => 'active',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_tutor_portal(): void
    {
        $response = $this->get(route('tutor.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_redirect_routes_tutor_to_tutor_dashboard(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('dashboard'));
        $response->assertRedirect(route('tutor.dashboard'));
    }

    public function test_tutor_can_access_dashboard_and_see_assigned_classes(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('tutor.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang, Tutor Arya Wijaya');
        $response->assertSee('Matematika SMA Kelas 12');
        $response->assertDontSee('Fisika Dasar Kelas 10'); // Tutor B's class
    }

    public function test_tutor_sees_today_teaching_session_on_dashboard(): void
    {
        $session = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'schedule_id' => $this->scheduleMathMonday->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang 101',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->tutorA)->get(route('tutor.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Ruang 101');
        $response->assertSee('16:00');
    }

    public function test_tutor_only_sees_their_assigned_schedules_on_schedules_index(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('tutor.schedules.index'));

        $response->assertStatus(200);
        $response->assertSee('Matematika SMA Kelas 12');
        $response->assertSee('Ruang 101');
        $response->assertDontSee('Fisika Dasar Kelas 10');
        $response->assertDontSee('Lab Fisika');
    }

    public function test_tutor_can_filter_schedules_by_day_of_week(): void
    {
        // Monday filter (day_of_week = 1) -> shows Math
        $responseMonday = $this->actingAs($this->tutorA)->get(route('tutor.schedules.index', [
            'day_of_week' => 1,
        ]));
        $responseMonday->assertStatus(200);
        $responseMonday->assertSee('Matematika SMA Kelas 12');

        // Tuesday filter (day_of_week = 2) -> Tutor A has no Tuesday classes
        $responseTuesday = $this->actingAs($this->tutorA)->get(route('tutor.schedules.index', [
            'day_of_week' => 2,
        ]));
        $responseTuesday->assertStatus(200);
        $responseTuesday->assertDontSee('Fisika Dasar Kelas 10');
    }

    public function test_tutor_can_view_teaching_sessions_view(): void
    {
        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'schedule_id' => $this->scheduleMathMonday->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => Carbon::now()->subDay()->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang 101',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->tutorA)->get(route('tutor.schedules.index', [
            'view' => 'sessions',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Daftar Sesi Mengajar');
        $response->assertSee('Matematika SMA Kelas 12');
        $response->assertSee('Selesai');
    }

    public function test_tutor_can_view_assigned_classes_list_and_details(): void
    {
        // Class list
        $responseList = $this->actingAs($this->tutorA)->get(route('tutor.classes.index'));
        $responseList->assertStatus(200);
        $responseList->assertSee('Matematika SMA Kelas 12');
        $responseList->assertDontSee('Fisika Dasar Kelas 10');

        // Class detail
        $responseShow = $this->actingAs($this->tutorA)->get(route('tutor.classes.show', $this->classMath));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Matematika SMA Kelas 12');
        $responseShow->assertSee('Johnathan Doe');
        $responseShow->assertSee('Robert Doe');
    }

    public function test_tutor_cannot_view_unassigned_class_details(): void
    {
        // Tutor A attempting to view Tutor B's class
        $response = $this->actingAs($this->tutorA)->get(route('tutor.classes.show', $this->classPhysics));
        $response->assertStatus(403);
    }

    public function test_tenant_isolation_prevents_accessing_other_tenant_class(): void
    {
        // Tutor A attempting to view other tenant's class
        $response = $this->actingAs($this->tutorA)->get(route('tutor.classes.show', $this->classOtherTenant));
        $response->assertStatus(404);
    }
}
