<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\HonorAssignment;
use App\Models\HonorCalculation;
use App\Models\HonorScheme;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\TutorAssignment;
use App\Models\TutorAttendance;
use App\Models\TutorReplacement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_uuid_primary_keys_are_36_characters(): void
    {
        $tenant = Tenant::create([
            'name' => 'Bimbel Hebat',
            'slug' => 'bimbel-hebat',
            'status' => 'active',
        ]);

        $this->assertIsString($tenant->id);
        $this->assertEquals(36, strlen($tenant->id));
        $this->assertTrue(Str::isUuid($tenant->id));

        $branch = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Cabang 1',
            'status' => 'active',
        ]);

        $this->assertIsString($branch->id);
        $this->assertEquals(36, strlen($branch->id));
        $this->assertTrue(Str::isUuid($branch->id));
    }

    public function test_all_cressco_model_relationships_work_correctly(): void
    {
        // 1. Tenant
        $tenant = Tenant::create([
            'name' => 'Cressco Academy Test',
            'slug' => 'cressco-test-'.Str::random(5),
            'status' => 'active',
        ]);

        // 2. Branch
        $branch = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Cabang Pusat Test',
            'code' => 'CP-01',
            'status' => 'active',
        ]);

        // 3. Users
        $owner = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner Test',
            'email' => 'owner.'.Str::random(5).'@test.com',
            'password' => Hash::make('secret'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin Test',
            'email' => 'admin.'.Str::random(5).'@test.com',
            'password' => Hash::make('secret'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $tutor = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Tutor Test',
            'email' => 'tutor.'.Str::random(5).'@test.com',
            'password' => Hash::make('secret'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        // BranchUser
        $branchUser = BranchUser::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'user_id' => $admin->id,
        ]);

        $this->assertTrue($admin->branches->contains($branch));
        $this->assertTrue($branch->users->contains($admin));

        // 4. Student
        $student = Student::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Student Test',
            'status' => 'active',
        ]);

        // 5. Classes
        $class = Classes::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Kelas Matematika 10',
            'subject' => 'Matematika',
            'status' => 'active',
        ]);

        // 6. Enrollment
        $enrollment = Enrollment::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'student_id' => $student->id,
            'class_id' => $class->id,
            'started_at' => '2026-01-01',
            'status' => 'active',
        ]);

        $this->assertTrue($student->classes->contains($class));
        $this->assertTrue($class->students->contains($student));

        // 7. Tutor Assignment
        $tutorAssignment = TutorAssignment::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'tutor_id' => $tutor->id,
            'class_id' => $class->id,
            'status' => 'active',
        ]);

        $this->assertTrue($tutor->assignedClasses->contains($class));
        $this->assertTrue($class->tutors->contains($tutor));

        // 8. Schedule
        $schedule = Schedule::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'class_id' => $class->id,
            'scheduled_tutor_id' => $tutor->id,
            'day_of_week' => 1,
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'starts_on' => '2026-01-01',
            'status' => 'active',
        ]);

        // 9. Teaching Session
        $session = TeachingSession::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'schedule_id' => $schedule->id,
            'class_id' => $class->id,
            'scheduled_tutor_id' => $tutor->id,
            'actual_tutor_id' => $tutor->id,
            'session_date' => '2026-02-02',
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'status' => 'completed',
        ]);

        // 10. Student Attendance & Tutor Attendance
        $studentAtt = StudentAttendance::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'teaching_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'hadir',
            'recorded_at' => now(),
            'recorded_by' => $tutor->id,
        ]);

        $tutorAtt = TutorAttendance::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'teaching_session_id' => $session->id,
            'tutor_id' => $tutor->id,
            'status' => 'present',
            'recorded_at' => now(),
        ]);

        $this->assertEquals($studentAtt->id, $session->studentAttendances->first()->id);
        $this->assertEquals($tutorAtt->id, $session->tutorAttendance->id);

        // 11. Assessment & Result
        $assessment = Assessment::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'class_id' => $class->id,
            'name' => 'Ulangan Harian 1',
            'type' => 'ujian',
            'assessment_date' => '2026-02-02',
            'max_score' => 100.00,
            'created_by' => $tutor->id,
        ]);

        $result = AssessmentResult::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'assessment_id' => $assessment->id,
            'student_id' => $student->id,
            'score' => 90.00,
        ]);

        $this->assertEquals($result->id, $assessment->results->first()->id);
        $this->assertEquals($result->id, $student->assessmentResults->first()->id);

        // 12. Payment
        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'period' => '2026-02',
            'amount' => 500000.00,
            'due_date' => '2026-02-10',
            'status' => 'lunas',
            'recorded_by' => $admin->id,
        ]);

        $this->assertEquals($payment->id, $student->payments->first()->id);
        $this->assertEquals($payment->id, $enrollment->payments->first()->id);

        // 13. Honor Scheme & Assignment & Calculation
        $scheme = HonorScheme::create([
            'tenant_id' => $tenant->id,
            'name' => 'Honor Per Sesi Test',
            'method' => 'per_session',
            'rate' => 150000.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        $honorAssignment = HonorAssignment::create([
            'tenant_id' => $tenant->id,
            'tutor_id' => $tutor->id,
            'honor_scheme_id' => $scheme->id,
            'assignment_type' => 'tutor_override',
            'effective_from' => '2026-01-01',
        ]);

        $honorCalc = HonorCalculation::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'tutor_id' => $tutor->id,
            'honor_scheme_id' => $scheme->id,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'method' => 'per_session',
            'base_amount' => 150000.00,
            'adjustment_amount' => 0.00,
            'final_amount' => 150000.00,
            'status' => 'draft',
            'calculated_by' => $admin->id,
        ]);

        $this->assertEquals($scheme->id, $honorAssignment->honorScheme->id);
        $this->assertEquals($honorCalc->id, $tutor->tutorHonorCalculations->first()->id);

        // 14. Tutor Replacement
        $replacement = TutorReplacement::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'teaching_session_id' => $session->id,
            'scheduled_tutor_id' => $tutor->id,
            'replacement_tutor_id' => $tutor->id,
            'reason' => 'Pergantian jadwal internal',
            'changed_by' => $admin->id,
            'changed_at' => now(),
        ]);

        $this->assertEquals($replacement->id, $session->tutorReplacements->first()->id);

        // 15. Tenant Setting & Audit Log
        $setting = TenantSetting::create([
            'tenant_id' => $tenant->id,
            'key' => 'test_key',
            'value' => ['enabled' => true],
        ]);

        $audit = AuditLog::create([
            'tenant_id' => $tenant->id,
            'actor_user_id' => $admin->id,
            'action' => 'test.action',
            'entity_type' => 'Classes',
            'entity_id' => $class->id,
        ]);

        $this->assertEquals($setting->id, $tenant->settings->first()->id);
        $this->assertEquals($audit->id, $tenant->auditLogs->first()->id);
    }

    public function test_tenant_isolation_composite_foreign_key_rejects_cross_tenant_references(): void
    {
        // Tenant A
        $tenantA = Tenant::create([
            'name' => 'Tenant A',
            'slug' => 'tenant-a',
            'status' => 'active',
        ]);

        $branchA = Branch::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Cabang A',
            'status' => 'active',
        ]);

        // Tenant B
        $tenantB = Tenant::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b',
            'status' => 'active',
        ]);

        $branchB = Branch::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Cabang B',
            'status' => 'active',
        ]);

        // Attempting to create a Student in Tenant A with Branch B (belonging to Tenant B)
        // MUST fail due to composite foreign key: (branch_id, tenant_id) REFERENCES branches(id, tenant_id)
        $this->expectException(QueryException::class);

        Student::create([
            'tenant_id' => $tenantA->id,
            'branch_id' => $branchB->id, // Cross-tenant!
            'name' => 'Hacker Student',
            'status' => 'active',
        ]);
    }

    public function test_enrollment_rejects_cross_tenant_student_and_class(): void
    {
        $tenantA = Tenant::create([
            'name' => 'Tenant A',
            'slug' => 'tenant-a-enrollment',
            'status' => 'active',
        ]);

        $branchA = Branch::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Cabang A',
            'status' => 'active',
        ]);

        $studentA = Student::create([
            'tenant_id' => $tenantA->id,
            'branch_id' => $branchA->id,
            'name' => 'Student A',
            'status' => 'active',
        ]);

        $tenantB = Tenant::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b-enrollment',
            'status' => 'active',
        ]);

        $branchB = Branch::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Cabang B',
            'status' => 'active',
        ]);

        $classB = Classes::create([
            'tenant_id' => $tenantB->id,
            'branch_id' => $branchB->id,
            'name' => 'Class B',
            'status' => 'active',
        ]);

        // Attempting to enroll Student A into Class B under Tenant A
        // MUST fail due to composite foreign key: (class_id, tenant_id) REFERENCES classes(id, tenant_id)
        $this->expectException(QueryException::class);

        Enrollment::create([
            'tenant_id' => $tenantA->id,
            'branch_id' => $branchA->id,
            'student_id' => $studentA->id,
            'class_id' => $classB->id, // Class belongs to Tenant B!
            'started_at' => '2026-01-01',
            'status' => 'active',
        ]);
    }

    public function test_unique_constraints_are_enforced(): void
    {
        $tenant = Tenant::create([
            'name' => 'Unique Tenant',
            'slug' => 'unique-slug',
            'status' => 'active',
        ]);

        // Duplicate slug fails
        try {
            Tenant::create([
                'name' => 'Duplicate Tenant',
                'slug' => 'unique-slug',
                'status' => 'active',
            ]);
            $this->fail('Expected unique constraint failure for tenant slug');
        } catch (QueryException|UniqueConstraintViolationException $e) {
            $this->assertTrue(true);
        }

        // Duplicate email fails
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'User 1',
            'email' => 'duplicate@test.com',
            'password' => Hash::make('secret'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        try {
            User::create([
                'tenant_id' => $tenant->id,
                'name' => 'User 2',
                'email' => 'duplicate@test.com',
                'password' => Hash::make('secret'),
                'role' => 'admin',
                'status' => 'active',
            ]);
            $this->fail('Expected unique constraint failure for user email');
        } catch (QueryException|UniqueConstraintViolationException $e) {
            $this->assertTrue(true);
        }
    }
}
