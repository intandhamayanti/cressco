<?php

namespace Tests\Feature\Tutor;

use App\Models\Assessment;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TutorAssessmentAndHistoryTest extends TestCase
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

    protected Student $studentJohn;

    protected Student $studentJane;

    protected Assessment $assessmentMathQuiz;

    protected Assessment $assessmentPhysicsTest;

    protected TeachingSession $completedSessionMath;

    protected TeachingSession $scheduledSessionMath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Tutoring',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Apex Learning',
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

        // Tutor B (Other tutor in same tenant)
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

        // Students in Math class
        $this->studentJohn = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Johnathan Doe',
            'nis' => 'PA-MLG-001',
            'grade' => '12 SMA',
            'status' => 'active',
        ]);

        $this->studentJane = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Jane Smith',
            'nis' => 'PA-MLG-002',
            'grade' => '12 SMA',
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

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $this->studentJane->id,
            'class_id' => $this->classMath->id,
            'started_at' => now()->subMonth()->toDateString(),
            'status' => 'active',
        ]);

        // Assessments
        $this->assessmentMathQuiz = Assessment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'name' => 'Quiz 1 Turunan Aljabar',
            'type' => 'quiz',
            'material' => 'Bab 3 Turunan',
            'assessment_date' => Carbon::yesterday()->toDateString(),
            'max_score' => 100,
            'notes' => 'Quiz pemahaman konsep',
            'created_by' => $this->tutorA->id,
        ]);

        $this->assessmentPhysicsTest = Assessment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'class_id' => $this->classPhysics->id,
            'name' => 'Ujian Tengah Bab Dinamika',
            'type' => 'ujian',
            'material' => 'Hukum Newton',
            'assessment_date' => Carbon::yesterday()->toDateString(),
            'max_score' => 100,
            'created_by' => $this->tutorB->id,
        ]);

        // Teaching Sessions for History
        $this->completedSessionMath = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => Carbon::yesterday()->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang 101',
            'status' => 'completed',
            'material' => 'Integral Parsial & Latihan Soal Mandiri',
            'notes' => 'Siswa menyelesaikan 4 nomor latihan',
        ]);

        $this->scheduledSessionMath = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => Carbon::tomorrow()->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang 101',
            'status' => 'scheduled',
        ]);
    }

    public function test_tutor_can_list_assessments_in_teaching_scope(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('tutor.assessments.index'));

        $response->assertStatus(200);
        $response->assertSee('Quiz 1 Turunan Aljabar');
        $response->assertDontSee('Ujian Tengah Bab Dinamika'); // Tutor B's class
    }

    public function test_tutor_can_create_assessment_for_assigned_class(): void
    {
        $response = $this->actingAs($this->tutorA)->post(route('tutor.assessments.store'), [
            'class_id' => $this->classMath->id,
            'name' => 'Tugas 2 Integral Trigonometri',
            'type' => 'tugas',
            'material' => 'Bab 4 Integral Lanjut',
            'assessment_date' => Carbon::today()->toDateString(),
            'max_score' => 100,
            'notes' => 'Dikumpulkan minggu depan',
        ]);

        $this->assertDatabaseHas('assessments', [
            'tenant_id' => $this->tenant->id,
            'class_id' => $this->classMath->id,
            'name' => 'Tugas 2 Integral Trigonometri',
            'type' => 'tugas',
            'created_by' => $this->tutorA->id,
        ]);
    }

    public function test_tutor_cannot_create_assessment_for_unassigned_class(): void
    {
        $response = $this->actingAs($this->tutorA)->post(route('tutor.assessments.store'), [
            'class_id' => $this->classPhysics->id,
            'name' => 'Tugas Ilegal',
            'type' => 'tugas',
            'assessment_date' => Carbon::today()->toDateString(),
            'max_score' => 100,
        ]);

        $response->assertStatus(403);
    }

    public function test_tutor_can_view_assessment_details_and_student_list(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('tutor.assessments.show', $this->assessmentMathQuiz));

        $response->assertStatus(200);
        $response->assertSee('Quiz 1 Turunan Aljabar');
        $response->assertSee('Johnathan Doe');
        $response->assertSee('Jane Smith');
        $response->assertSee('Simpan Nilai Siswa');
    }

    public function test_tutor_can_input_and_update_student_assessment_scores(): void
    {
        $response = $this->actingAs($this->tutorA)->post(route('tutor.assessments.results.store', $this->assessmentMathQuiz), [
            'results' => [
                [
                    'student_id' => $this->studentJohn->id,
                    'score' => 88.5,
                    'notes' => 'Sangat baik, rumus dipahami',
                ],
                [
                    'student_id' => $this->studentJane->id,
                    'score' => 95.0,
                    'notes' => 'Sempurna',
                ],
            ],
        ]);

        $response->assertRedirect(route('tutor.assessments.show', $this->assessmentMathQuiz));

        $this->assertDatabaseHas('assessment_results', [
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $this->assessmentMathQuiz->id,
            'student_id' => $this->studentJohn->id,
            'score' => 88.5,
            'notes' => 'Sangat baik, rumus dipahami',
        ]);

        $this->assertDatabaseHas('assessment_results', [
            'tenant_id' => $this->tenant->id,
            'assessment_id' => $this->assessmentMathQuiz->id,
            'student_id' => $this->studentJane->id,
            'score' => 95.0,
            'notes' => 'Sempurna',
        ]);
    }

    public function test_tutor_can_update_assessment_meta_info(): void
    {
        $response = $this->actingAs($this->tutorA)->put(route('tutor.assessments.update', $this->assessmentMathQuiz), [
            'name' => 'Quiz 1 Turunan Aljabar (Revisi Bobot)',
            'type' => 'quiz',
            'material' => 'Bab 3 Turunan & Limit',
            'assessment_date' => Carbon::yesterday()->toDateString(),
            'max_score' => 100,
            'notes' => 'Sudah direvisi',
        ]);

        $response->assertRedirect(route('tutor.assessments.show', $this->assessmentMathQuiz));

        $this->assessmentMathQuiz->refresh();
        $this->assertEquals('Quiz 1 Turunan Aljabar (Revisi Bobot)', $this->assessmentMathQuiz->name);
        $this->assertEquals('Bab 3 Turunan & Limit', $this->assessmentMathQuiz->material);
    }

    public function test_tutor_can_view_teaching_history_with_completed_sessions(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('tutor.history.index'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Mengajar');
        $response->assertSee('Integral Parsial & Latihan Soal Mandiri');
        $response->assertSee('90 Menit');
        // Scheduled session should NOT be in history index
        $response->assertDontSee('tomorrow');
    }

    public function test_tutor_cannot_view_unassigned_assessment(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('tutor.assessments.show', $this->assessmentPhysicsTest));
        $response->assertStatus(404);
    }

    public function test_tenant_isolation_protects_other_tenant_assessments(): void
    {
        $tutorOther = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Tutor Other',
            'email' => 'other.tutor@apex.test',
            'role' => 'tutor',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
            'email_verified_at' => now(),
        ]);

        $assessmentOther = Assessment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->classOtherTenant->branch_id,
            'class_id' => $this->classOtherTenant->id,
            'name' => 'Kimia Organik Test',
            'type' => 'quiz',
            'assessment_date' => Carbon::today()->toDateString(),
            'max_score' => 100,
            'created_by' => $tutorOther->id,
        ]);

        $response = $this->actingAs($this->tutorA)->get(route('tutor.assessments.show', $assessmentOther));
        $response->assertStatus(404);
    }
}
