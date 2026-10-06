<?php

namespace Tests\Feature\Tutor;

use App\Models\Assessment;
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

class TutorTeachingScopeScopingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Branch $branch;

    protected User $tutorBambang;

    protected User $tutorDewi;

    protected Classes $classIllustration;

    protected Classes $classGraphicDesign;

    protected Student $studentAlex;

    protected TeachingSession $dewiNormalSession;

    protected TeachingSession $bambangNormalSession;

    protected TeachingSession $dewiReplacedByBambangSession;

    protected Schedule $bambangSchedule;

    protected Schedule $dewiSchedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Tutoring',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Prime Academy - Semarang',
            'code' => 'SMG-01',
            'status' => 'active',
        ]);

        // Tutor 1: Bambang Wijaya
        $this->tutorBambang = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Bambang Wijaya, S.Sn.',
            'email' => 'tutor.bambang@cressco.test',
            'role' => 'tutor',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
            'email_verified_at' => now(),
        ]);

        // Tutor 2: Dewi Lestari
        $this->tutorDewi = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Dewi Lestari, M.Ds.',
            'email' => 'tutor.dewi@cressco.test',
            'role' => 'tutor',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
            'email_verified_at' => now(),
        ]);

        // Class 1: Digital Illustration (Taught by Bambang)
        $this->classIllustration = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Digital Illustration',
            'subject' => 'Art',
            'level' => 'Umum',
            'capacity' => 15,
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'tutor_id' => $this->tutorBambang->id,
            'class_id' => $this->classIllustration->id,
            'started_at' => now()->subMonth()->toDateString(),
            'status' => 'active',
        ]);

        // Class 2: Graphic Design (Taught by Dewi)
        $this->classGraphicDesign = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Graphic Design',
            'subject' => 'Design',
            'level' => 'Umum',
            'capacity' => 15,
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'tutor_id' => $this->tutorDewi->id,
            'class_id' => $this->classGraphicDesign->id,
            'started_at' => now()->subMonth()->toDateString(),
            'status' => 'active',
        ]);

        // Student
        $this->studentAlex = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Alex Turner',
            'nis' => 'PA-SMG-001',
            'grade' => '12 SMA',
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $this->studentAlex->id,
            'class_id' => $this->classIllustration->id,
            'started_at' => now()->subMonth()->toDateString(),
            'status' => 'active',
        ]);

        // Schedules
        $this->bambangSchedule = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classIllustration->id,
            'scheduled_tutor_id' => $this->tutorBambang->id,
            'day_of_week' => 5, // Friday
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Art Studio S',
            'starts_on' => now()->subMonths(2)->toDateString(),
            'status' => 'active',
        ]);

        $this->dewiSchedule = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classGraphicDesign->id,
            'scheduled_tutor_id' => $this->tutorDewi->id,
            'day_of_week' => 6, // Saturday
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'room' => 'Studio Desain P',
            'starts_on' => now()->subMonths(2)->toDateString(),
            'status' => 'active',
        ]);

        $today = Carbon::today();

        // 1. Dewi's normal session (Dewi scheduled & actual)
        $this->dewiNormalSession = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'schedule_id' => $this->dewiSchedule->id,
            'class_id' => $this->classGraphicDesign->id,
            'scheduled_tutor_id' => $this->tutorDewi->id,
            'actual_tutor_id' => $this->tutorDewi->id,
            'session_date' => $today->copy()->addDays(5)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'room' => 'Studio Desain P',
            'status' => 'scheduled',
            'material' => 'Pengenalan Vector Graphic',
        ]);

        // 2. Bambang's normal session (Bambang scheduled & actual)
        $this->bambangNormalSession = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'schedule_id' => $this->bambangSchedule->id,
            'class_id' => $this->classIllustration->id,
            'scheduled_tutor_id' => $this->tutorBambang->id,
            'actual_tutor_id' => $this->tutorBambang->id,
            'session_date' => $today->copy()->addDays(4)->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Art Studio S',
            'status' => 'scheduled',
            'material' => 'Teknik Shading Digital',
        ]);

        // 3. Replacement session (Dewi scheduled, Bambang assigned as actual replacement tutor)
        $this->dewiReplacedByBambangSession = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classGraphicDesign->id,
            'scheduled_tutor_id' => $this->tutorDewi->id,
            'actual_tutor_id' => $this->tutorBambang->id,
            'session_date' => $today->copy()->addDays(2)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'room' => 'Studio Desain P',
            'status' => 'scheduled',
            'material' => 'Color Grading Workshop',
            'notes' => '[TUTOR PENGGANTI] Ditugaskan untuk menggantikan Dewi Lestari',
        ]);
    }

    public function test_bambang_cannot_see_dewi_session_in_sessions_list(): void
    {
        $response = $this->actingAs($this->tutorBambang)->get(route('tutor.sessions.index'));

        $response->assertStatus(200);
        // Bambang sees his own session
        $response->assertSee('Teknik Shading Digital');
        $response->assertSee('Digital Illustration');

        // Bambang sees session where he is actual replacement tutor
        $response->assertSee('Color Grading Workshop');
        $response->assertSee('Anda Tutor Pengganti');

        // Bambang MUST NOT see Dewi's normal session
        $response->assertDontSee('Pengenalan Vector Graphic');
    }

    public function test_dewi_sees_her_scheduled_sessions_including_when_replaced(): void
    {
        $response = $this->actingAs($this->tutorDewi)->get(route('tutor.sessions.index'));

        $response->assertStatus(200);
        // Dewi sees her normal session
        $response->assertSee('Pengenalan Vector Graphic');
        $response->assertSee('Graphic Design');

        // Dewi sees the session where she is scheduled tutor even if replaced
        $response->assertSee('Color Grading Workshop');

        // Dewi MUST NOT see Bambang's normal session
        $response->assertDontSee('Teknik Shading Digital');
    }

    public function test_tutor_cannot_access_other_tutors_session_detail_directly(): void
    {
        // Bambang attempting to access Dewi's normal session directly via URL
        $response = $this->actingAs($this->tutorBambang)->get(route('tutor.sessions.show', $this->dewiNormalSession));
        $response->assertStatus(404);

        // Dewi attempting to access Bambang's normal session directly via URL
        $response = $this->actingAs($this->tutorDewi)->get(route('tutor.sessions.show', $this->bambangNormalSession));
        $response->assertStatus(404);
    }

    public function test_tutor_can_access_their_own_session_detail(): void
    {
        $response = $this->actingAs($this->tutorBambang)->get(route('tutor.sessions.show', $this->bambangNormalSession));
        $response->assertStatus(200);
        $response->assertSee('Teknik Shading Digital');
    }

    public function test_replacement_tutor_and_scheduled_tutor_both_can_access_replacement_session_detail(): void
    {
        // Bambang (actual tutor) can access
        $responseBambang = $this->actingAs($this->tutorBambang)->get(route('tutor.sessions.show', $this->dewiReplacedByBambangSession));
        $responseBambang->assertStatus(200);
        $responseBambang->assertSee('Color Grading Workshop');

        // Dewi (scheduled tutor) can access
        $responseDewi = $this->actingAs($this->tutorDewi)->get(route('tutor.sessions.show', $this->dewiReplacedByBambangSession));
        $responseDewi->assertStatus(200);
        $responseDewi->assertSee('Color Grading Workshop');
    }

    public function test_tutor_cannot_update_other_tutors_session(): void
    {
        $response = $this->actingAs($this->tutorBambang)->put(route('tutor.sessions.update', $this->dewiNormalSession), [
            'material' => 'Hacked Material',
        ]);

        $this->assertTrue(in_array($response->status(), [403, 404], true));
        $this->assertDatabaseMissing('teaching_sessions', [
            'id' => $this->dewiNormalSession->id,
            'material' => 'Hacked Material',
        ]);
    }

    public function test_tutor_dashboard_scopes_sessions_and_schedules(): void
    {
        $response = $this->actingAs($this->tutorBambang)->get(route('tutor.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Digital Illustration');
        $response->assertDontSee('Pengenalan Vector Graphic');
    }

    public function test_tutor_schedules_scopes_weekly_schedules(): void
    {
        $response = $this->actingAs($this->tutorBambang)->get(route('tutor.schedules.index'));

        $response->assertStatus(200);
        $response->assertSee('Art Studio S');
        $response->assertDontSee('Studio Desain P');
    }

    public function test_tutor_cannot_access_other_tutors_assigned_class(): void
    {
        // Bambang attempting to access Graphic Design class (assigned to Dewi)
        $response = $this->actingAs($this->tutorBambang)->get(route('tutor.classes.show', $this->classGraphicDesign));
        $response->assertStatus(403);
    }

    public function test_tutor_assessment_scoped_to_assigned_classes(): void
    {
        // Assessment in Graphic Design (Dewi's class)
        $dewiAssessment = Assessment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classGraphicDesign->id,
            'name' => 'Tugas Layout Poster',
            'type' => 'tugas',
            'assessment_date' => now()->toDateString(),
            'max_score' => 100,
            'created_by' => $this->tutorDewi->id,
        ]);

        // Bambang views assessment list
        $responseList = $this->actingAs($this->tutorBambang)->get(route('tutor.assessments.index'));
        $responseList->assertStatus(200);
        $responseList->assertDontSee('Tugas Layout Poster');

        // Bambang attempting to view Dewi's assessment detail
        $responseShow = $this->actingAs($this->tutorBambang)->get(route('tutor.assessments.show', $dewiAssessment));
        $responseShow->assertStatus(404);
    }
}
