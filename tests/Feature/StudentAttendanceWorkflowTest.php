<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use App\Services\TeachingSessionGenerationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentAttendanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Branch $branchMalang;

    protected Branch $branchSurabaya;

    protected Branch $branchTenantB;

    protected User $tutorA;

    protected User $tutorB;

    protected User $tutorTenantB;

    protected Classes $classMath;

    protected Classes $classEnglish;

    protected Classes $classTenantB;

    protected Student $studentAhmad;

    protected Student $studentBudi;

    protected Student $studentCitra;

    protected Student $studentDewi;

    protected Student $studentTenantB;

    protected TeachingSession $sessionMath;

    protected TeachingSession $sessionEnglish;

    protected function setUp(): void
    {
        parent::setUp();

        // Tenants
        $this->tenantA = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Tutoring',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Nexus Bimbel Edu',
            'slug' => 'nexus-edu',
            'status' => 'active',
        ]);

        // Branches
        $this->branchMalang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Cabang Suhat Malang',
            'code' => 'MLG-01',
            'status' => 'active',
        ]);

        $this->branchSurabaya = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Cabang Gubeng Surabaya',
            'code' => 'SBY-01',
            'status' => 'active',
        ]);

        $this->branchTenantB = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Cabang Bandung',
            'code' => 'BDG-01',
            'status' => 'active',
        ]);

        // Tutors
        $this->tutorA = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Tutor Arya Matematika',
            'email' => 'arya.math@prime.test',
            'password' => Hash::make('password123'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->tutorB = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Tutor Bella Bahasa',
            'email' => 'bella.english@prime.test',
            'password' => Hash::make('password123'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->tutorTenantB = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Tutor Tenant B',
            'email' => 'tutor.b@nexus.test',
            'password' => Hash::make('password123'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        // Classes
        $this->classMath = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'name' => '12 SMA Matematika Saintek',
            'subject' => 'Matematika',
            'status' => 'active',
        ]);

        $this->classEnglish = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchSurabaya->id,
            'name' => '11 SMA Bahasa Inggris TOEFL',
            'subject' => 'Bahasa Inggris',
            'status' => 'active',
        ]);

        $this->classTenantB = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchTenantB->id,
            'name' => '10 SMA Fisika Tenant B',
            'subject' => 'Fisika',
            'status' => 'active',
        ]);

        // Tutor Assignments
        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'tutor_id' => $this->tutorA->id,
            'started_at' => Carbon::now()->subMonths(2),
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchSurabaya->id,
            'class_id' => $this->classEnglish->id,
            'tutor_id' => $this->tutorB->id,
            'started_at' => Carbon::now()->subMonths(2),
            'status' => 'active',
        ]);

        // Students in Tenant A
        $this->studentAhmad = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Ahmad Dahlan',
            'nis' => 'PA-001',
            'status' => 'active',
        ]);

        $this->studentBudi = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Budi Santoso',
            'nis' => 'PA-002',
            'status' => 'active',
        ]);

        $this->studentCitra = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Citra Kirana',
            'nis' => 'PA-003',
            'status' => 'active',
        ]);

        $this->studentDewi = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchSurabaya->id,
            'name' => 'Dewi Sartika',
            'nis' => 'PA-004',
            'status' => 'active',
        ]);

        // Student in Tenant B
        $this->studentTenantB = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchTenantB->id,
            'name' => 'Siswa Tenant B',
            'nis' => 'NX-001',
            'status' => 'active',
        ]);

        // Enrollments in Class Math (Ahmad, Budi, Citra)
        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'student_id' => $this->studentAhmad->id,
            'started_at' => Carbon::now()->subMonths(2),
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'student_id' => $this->studentBudi->id,
            'started_at' => Carbon::now()->subMonths(2),
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'student_id' => $this->studentCitra->id,
            'started_at' => Carbon::now()->subMonths(2),
            'status' => 'active',
        ]);

        // Enrollment in Class English (Dewi)
        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchSurabaya->id,
            'class_id' => $this->classEnglish->id,
            'student_id' => $this->studentDewi->id,
            'started_at' => Carbon::now()->subMonths(2),
            'status' => 'active',
        ]);

        // Recurring Schedule generated from Phase 7.1 workflow
        $scheduleMath = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => Carbon::today()->dayOfWeek,
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'room' => 'Ruang Teori 1',
            'starts_on' => Carbon::today()->toDateString(),
            'status' => 'active',
        ]);

        // Generate sessions via Phase 7.1 service
        $genResult = app(TeachingSessionGenerationService::class)->generateForSchedule(
            $scheduleMath,
            Carbon::today(),
            Carbon::today()
        );

        $this->sessionMath = $genResult['sessions']->first();

        // English session for Tutor B
        $this->sessionEnglish = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchSurabaya->id,
            'class_id' => $this->classEnglish->id,
            'scheduled_tutor_id' => $this->tutorB->id,
            'actual_tutor_id' => $this->tutorB->id,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '17:00:00',
            'end_time' => '18:30:00',
            'room' => 'Ruang Bahasa',
            'status' => 'scheduled',
        ]);
    }

    public function test_tutor_can_input_student_attendances_for_teaching_session_in_scope(): void
    {
        $this->actingAs($this->tutorA);

        $payload = [
            'attendances' => [
                [
                    'student_id' => $this->studentAhmad->id,
                    'status' => 'hadir',
                    'note' => 'Tepat waktu',
                ],
                [
                    'student_id' => $this->studentBudi->id,
                    'status' => 'izin',
                    'note' => 'Izin keluarga',
                ],
                [
                    'student_id' => $this->studentCitra->id,
                    'status' => 'sakit',
                    'note' => 'Surat dokter terlampir',
                ],
            ],
            'material' => 'Matriks & Transformasi Linear',
            'session_notes' => 'Semua siswa mengerjakan latihan',
            'mark_session_completed' => 1,
        ];

        $response = $this->post(route('tutor.sessions.attendances.store', $this->sessionMath), $payload);

        $response->assertRedirect(route('tutor.sessions.show', $this->sessionMath));
        $response->assertSessionHas('success');

        // Check 3 student attendance records in database
        $this->assertDatabaseHas('student_attendances', [
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'teaching_session_id' => $this->sessionMath->id,
            'student_id' => $this->studentAhmad->id,
            'status' => 'hadir',
            'note' => 'Tepat waktu',
            'recorded_by' => $this->tutorA->id,
        ]);

        $this->assertDatabaseHas('student_attendances', [
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'teaching_session_id' => $this->sessionMath->id,
            'student_id' => $this->studentBudi->id,
            'status' => 'izin',
            'note' => 'Izin keluarga',
            'recorded_by' => $this->tutorA->id,
        ]);

        $this->assertDatabaseHas('student_attendances', [
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'teaching_session_id' => $this->sessionMath->id,
            'student_id' => $this->studentCitra->id,
            'status' => 'sakit',
            'note' => 'Surat dokter terlampir',
            'recorded_by' => $this->tutorA->id,
        ]);

        // Session status is updated to completed with material
        $this->sessionMath->refresh();
        $this->assertEquals('completed', $this->sessionMath->status);
        $this->assertEquals('Matriks & Transformasi Linear', $this->sessionMath->material);
        $this->assertEquals($this->tutorA->id, $this->sessionMath->actual_tutor_id);
    }

    public function test_all_valid_attendance_statuses_are_supported(): void
    {
        $this->actingAs($this->tutorA);

        $payload = [
            'attendances' => [
                [
                    'student_id' => $this->studentAhmad->id,
                    'status' => 'hadir',
                ],
                [
                    'student_id' => $this->studentBudi->id,
                    'status' => 'alpa',
                    'note' => 'Tanpa keterangan',
                ],
            ],
        ];

        $response = $this->post(route('tutor.sessions.attendances.store', $this->sessionMath), $payload);
        $response->assertRedirect(route('tutor.sessions.show', $this->sessionMath));

        $this->assertDatabaseHas('student_attendances', [
            'teaching_session_id' => $this->sessionMath->id,
            'student_id' => $this->studentBudi->id,
            'status' => 'alpa',
        ]);
    }

    public function test_invalid_attendance_status_is_rejected_by_validation(): void
    {
        $this->actingAs($this->tutorA);

        $payload = [
            'attendances' => [
                [
                    'student_id' => $this->studentAhmad->id,
                    'status' => 'invalid_status_xyz',
                ],
            ],
        ];

        $response = $this->post(route('tutor.sessions.attendances.store', $this->sessionMath), $payload);
        $response->assertSessionHasErrors('attendances.0.status');
    }

    public function test_submitting_attendance_again_updates_existing_records_without_duplicates(): void
    {
        $this->actingAs($this->tutorA);

        // First submission
        $this->post(route('tutor.sessions.attendances.store', $this->sessionMath), [
            'attendances' => [
                [
                    'student_id' => $this->studentAhmad->id,
                    'status' => 'hadir',
                    'note' => 'First note',
                ],
            ],
        ]);

        $this->assertEquals(1, StudentAttendance::where('teaching_session_id', $this->sessionMath->id)->count());

        // Re-submission with updated status and note
        $this->post(route('tutor.sessions.attendances.store', $this->sessionMath), [
            'attendances' => [
                [
                    'student_id' => $this->studentAhmad->id,
                    'status' => 'izin',
                    'note' => 'Updated note: Izin sakit kepala',
                ],
            ],
        ]);

        // Still only 1 record for this student and session (no duplicate)
        $this->assertEquals(1, StudentAttendance::where('teaching_session_id', $this->sessionMath->id)->count());

        $attendance = StudentAttendance::where('teaching_session_id', $this->sessionMath->id)->first();
        $this->assertEquals('izin', $attendance->status);
        $this->assertEquals('Updated note: Izin sakit kepala', $attendance->note);
    }

    public function test_tutor_cannot_record_attendance_for_session_outside_teaching_scope(): void
    {
        $this->actingAs($this->tutorA);

        // Tutor A tries to submit attendance for Tutor B's English session
        $response = $this->post(route('tutor.sessions.attendances.store', $this->sessionEnglish), [
            'attendances' => [
                [
                    'student_id' => $this->studentDewi->id,
                    'status' => 'hadir',
                ],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_tutor_can_correct_single_student_attendance(): void
    {
        $attendance = StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'teaching_session_id' => $this->sessionMath->id,
            'student_id' => $this->studentAhmad->id,
            'status' => 'alpa',
            'recorded_at' => now(),
            'recorded_by' => $this->tutorA->id,
        ]);

        $this->actingAs($this->tutorA);

        $response = $this->put(route('tutor.attendances.update', $attendance), [
            'status' => 'hadir',
            'note' => 'Koreksi kehadiran: Terlambat karena kendala hujan',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $attendance->refresh();
        $this->assertEquals('hadir', $attendance->status);
        $this->assertEquals('Koreksi kehadiran: Terlambat karena kendala hujan', $attendance->note);
    }

    public function test_tutor_cannot_correct_attendance_for_session_outside_scope(): void
    {
        $attendanceEnglish = StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchSurabaya->id,
            'teaching_session_id' => $this->sessionEnglish->id,
            'student_id' => $this->studentDewi->id,
            'status' => 'alpa',
            'recorded_at' => now(),
            'recorded_by' => $this->tutorB->id,
        ]);

        $this->actingAs($this->tutorA);

        // Tutor A tries to correct Tutor B's student attendance
        $response = $this->put(route('tutor.attendances.update', $attendanceEnglish), [
            'status' => 'hadir',
            'note' => 'Unauthorized correction',
        ]);

        $response->assertForbidden();
    }

    public function test_tenant_isolation_prevents_attendance_cross_tenant_access(): void
    {
        $sessionTenantB = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchTenantB->id,
            'class_id' => $this->classTenantB->id,
            'scheduled_tutor_id' => $this->tutorTenantB->id,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'status' => 'scheduled',
        ]);

        $this->actingAs($this->tutorA);

        $response = $this->post(route('tutor.sessions.attendances.store', $sessionTenantB), [
            'attendances' => [
                [
                    'student_id' => $this->studentTenantB->id,
                    'status' => 'hadir',
                ],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_guest_cannot_record_or_correct_attendance(): void
    {
        $this->post(route('tutor.sessions.attendances.store', $this->sessionMath), [
            'attendances' => [
                ['student_id' => $this->studentAhmad->id, 'status' => 'hadir'],
            ],
        ])->assertRedirect(route('login'));
    }
}
