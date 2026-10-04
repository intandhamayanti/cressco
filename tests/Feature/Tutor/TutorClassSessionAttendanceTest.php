<?php

namespace Tests\Feature\Tutor;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TutorClassSessionAttendanceTest extends TestCase
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

    protected TeachingSession $sessionMath;

    protected TeachingSession $sessionPhysics;

    protected TeachingSession $sessionOtherTenant;

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

        // Teaching Sessions
        $this->sessionMath = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang 101',
            'status' => 'scheduled',
        ]);

        $this->sessionPhysics = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSurabaya->id,
            'class_id' => $this->classPhysics->id,
            'scheduled_tutor_id' => $this->tutorB->id,
            'actual_tutor_id' => $this->tutorB->id,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '18:00:00',
            'end_time' => '19:30:00',
            'room' => 'Lab Fisika',
            'status' => 'scheduled',
        ]);

        $tutorOtherTenant = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Tutor Other Tenant',
            'email' => 'other.tutor@apex.test',
            'role' => 'tutor',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
            'email_verified_at' => now(),
        ]);

        $this->sessionOtherTenant = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $otherBranch->id,
            'class_id' => $this->classOtherTenant->id,
            'scheduled_tutor_id' => $tutorOtherTenant->id,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'room' => 'Lab Kimia',
            'status' => 'scheduled',
        ]);
    }

    public function test_tutor_can_list_their_assigned_classes(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('tutor.classes.index'));

        $response->assertStatus(200);
        $response->assertSee('Matematika SMA Kelas 12');
        $response->assertDontSee('Fisika Dasar Kelas 10');
    }

    public function test_tutor_can_view_class_details_with_students_and_sessions(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('tutor.classes.show', $this->classMath));

        $response->assertStatus(200);
        $response->assertSee('Matematika SMA Kelas 12');
        $response->assertSee('Johnathan Doe');
        $response->assertSee('Jane Smith');
        $response->assertSee('Ruang 101');
    }

    public function test_tutor_can_list_teaching_sessions_in_scope(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('tutor.sessions.index'));

        $response->assertStatus(200);
        $response->assertSee('Matematika SMA Kelas 12');
        $response->assertDontSee('Fisika Dasar Kelas 10');
        $response->assertDontSee('Lab Fisika');
    }

    public function test_tutor_can_view_session_detail_and_attendance_form(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('tutor.sessions.show', $this->sessionMath));

        $response->assertStatus(200);
        $response->assertSee('Matematika SMA Kelas 12');
        $response->assertSee('Johnathan Doe');
        $response->assertSee('Jane Smith');
        $response->assertSee('Presensi Kehadiran Siswa');
    }

    public function test_tutor_can_update_session_material_and_notes(): void
    {
        $response = $this->actingAs($this->tutorA)->put(route('tutor.sessions.update', $this->sessionMath), [
            'material' => 'Bab 3 Integral Substitusi & Aljabar',
            'notes' => 'Siswa aktif bertanya, latihan 5 nomor selesai',
            'status' => 'completed',
        ]);

        $response->assertRedirect(route('tutor.sessions.show', $this->sessionMath));

        $this->sessionMath->refresh();
        $this->assertEquals('Bab 3 Integral Substitusi & Aljabar', $this->sessionMath->material);
        $this->assertEquals('Siswa aktif bertanya, latihan 5 nomor selesai', $this->sessionMath->notes);
        $this->assertEquals('completed', $this->sessionMath->status);
    }

    public function test_tutor_cannot_update_unassigned_session(): void
    {
        $response = $this->actingAs($this->tutorA)->put(route('tutor.sessions.update', $this->sessionPhysics), [
            'material' => 'Materi Hacker',
        ]);

        $response->assertStatus(403);
    }

    public function test_tutor_can_record_batch_student_attendance(): void
    {
        $response = $this->actingAs($this->tutorA)->post(route('tutor.sessions.attendances.store', $this->sessionMath), [
            'attendances' => [
                [
                    'student_id' => $this->studentJohn->id,
                    'status' => 'hadir',
                    'note' => 'Datang tepat waktu',
                ],
                [
                    'student_id' => $this->studentJane->id,
                    'status' => 'izin',
                    'note' => 'Izin lomba debat',
                ],
            ],
            'material' => 'Geometri Dimensi Tiga',
            'mark_session_completed' => 1,
        ]);

        $response->assertRedirect(route('tutor.sessions.show', $this->sessionMath));

        // Check student attendances created
        $this->assertDatabaseHas('student_attendances', [
            'tenant_id' => $this->tenant->id,
            'teaching_session_id' => $this->sessionMath->id,
            'student_id' => $this->studentJohn->id,
            'status' => 'hadir',
            'note' => 'Datang tepat waktu',
            'recorded_by' => $this->tutorA->id,
        ]);

        $this->assertDatabaseHas('student_attendances', [
            'tenant_id' => $this->tenant->id,
            'teaching_session_id' => $this->sessionMath->id,
            'student_id' => $this->studentJane->id,
            'status' => 'izin',
            'note' => 'Izin lomba debat',
            'recorded_by' => $this->tutorA->id,
        ]);

        // Check tutor attendance automatically recorded
        $this->assertDatabaseHas('tutor_attendances', [
            'tenant_id' => $this->tenant->id,
            'teaching_session_id' => $this->sessionMath->id,
            'tutor_id' => $this->tutorA->id,
            'status' => 'present',
            'source' => 'student_attendance_submission',
        ]);

        // Check session updated
        $this->sessionMath->refresh();
        $this->assertEquals('Geometri Dimensi Tiga', $this->sessionMath->material);
        $this->assertEquals('completed', $this->sessionMath->status);
    }

    public function test_tutor_actual_teaching_is_derived_from_teaching_session_and_student_attendance_submission(): void
    {
        // Session with no actual tutor initially
        $session = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => null,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'room' => 'Ruang 102',
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($this->tutorA)->post(route('tutor.sessions.attendances.store', $session), [
            'attendances' => [
                [
                    'student_id' => $this->studentJohn->id,
                    'status' => 'hadir',
                    'note' => 'Hadir aktif',
                ],
            ],
            'material' => 'Kalkulus Dasar & Turunan Fungsi',
            'mark_session_completed' => 1,
        ]);

        $response->assertRedirect(route('tutor.sessions.show', $session));

        $session->refresh();

        // Actual teaching is derived from the session and attendance submission
        $this->assertEquals($this->tutorA->id, $session->actual_tutor_id);
        $this->assertEquals('completed', $session->status);
        $this->assertEquals('Kalkulus Dasar & Turunan Fungsi', $session->material);

        // Verification that student attendance was recorded by this tutor
        $this->assertDatabaseHas('student_attendances', [
            'tenant_id' => $this->tenant->id,
            'teaching_session_id' => $session->id,
            'student_id' => $this->studentJohn->id,
            'status' => 'hadir',
            'recorded_by' => $this->tutorA->id,
        ]);
    }

    public function test_tutor_can_correct_student_attendance(): void
    {
        $attendance = StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'teaching_session_id' => $this->sessionMath->id,
            'student_id' => $this->studentJohn->id,
            'status' => 'alpa',
            'recorded_at' => now(),
            'recorded_by' => $this->tutorA->id,
        ]);

        $response = $this->actingAs($this->tutorA)->put(route('tutor.attendances.update', $attendance), [
            'status' => 'hadir',
            'note' => 'Koreksi: Siswa hadir telat 10 menit',
        ]);

        $response->assertRedirect();

        $attendance->refresh();
        $this->assertEquals('hadir', $attendance->status);
        $this->assertEquals('Koreksi: Siswa hadir telat 10 menit', $attendance->note);
    }

    public function test_tenant_isolation_protects_other_tenant_teaching_sessions(): void
    {
        $response = $this->actingAs($this->tutorA)->get(route('tutor.sessions.show', $this->sessionOtherTenant));
        $response->assertStatus(404);
    }
}
