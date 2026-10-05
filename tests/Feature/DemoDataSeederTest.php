<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\HonorCalculation;
use App\Models\HonorScheme;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAttendance;
use App\Models\TutorReplacement;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_seeder_populates_realistic_and_valid_dataset(): void
    {
        $this->seed(DemoDataSeeder::class);

        // 1. Tenant Verification
        $tenant = Tenant::where('slug', 'prime-academy')->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('Prime Academy Tutoring Center', $tenant->name);

        // 2. Branches Verification (3 branches)
        $branches = Branch::where('tenant_id', $tenant->id)->get();
        $this->assertCount(3, $branches);

        // 3. User Roles Verification
        $owner = User::where('tenant_id', $tenant->id)->where('role', 'owner')->first();
        $this->assertNotNull($owner);
        $this->assertEquals('owner@cressco.test', $owner->email);
        $this->assertTrue($owner->hasBranchAccess($branches->first()));

        $admins = User::where('tenant_id', $tenant->id)->where('role', 'admin')->get();
        $this->assertGreaterThanOrEqual(3, $admins->count());
        $this->assertLessThanOrEqual(4, $admins->count());

        $tutors = User::where('tenant_id', $tenant->id)->where('role', 'tutor')->get();
        $this->assertGreaterThanOrEqual(10, $tutors->count());
        $this->assertLessThanOrEqual(12, $tutors->count());

        // 4. Students & Classes Verification
        $students = Student::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(80, $students->count());
        $this->assertLessThanOrEqual(120, $students->count());

        $classes = Classes::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(12, $classes->count());
        $this->assertLessThanOrEqual(30, $classes->count());

        // 5. Enrollments Verification
        $enrollments = Enrollment::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(100, $enrollments->count());

        // Each tutor teaches 1-6 classes
        foreach ($tutors as $tutor) {
            $assignedClassCount = $tutor->tutorAssignments()->count();
            $this->assertGreaterThanOrEqual(1, $assignedClassCount, "Tutor {$tutor->name} has fewer than 1 assignments");
            $this->assertLessThanOrEqual(6, $assignedClassCount, "Tutor {$tutor->name} has more than 6 assignments");
        }

        // 6. Schedules & Teaching Sessions Verification
        $schedules = Schedule::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(15, $schedules->count());

        $sessions = TeachingSession::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(100, $sessions->count());

        $completedSessions = TeachingSession::where('tenant_id', $tenant->id)->where('status', 'completed')->get();
        $this->assertGreaterThanOrEqual(50, $completedSessions->count());

        // 7. Attendances & Replacements
        $studentAttendances = StudentAttendance::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(500, $studentAttendances->count());

        $tutorAttendances = TutorAttendance::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(50, $tutorAttendances->count());

        $replacements = TutorReplacement::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(1, $replacements->count());

        // 8. Assessments & Results
        $assessments = Assessment::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(10, $assessments->count());

        $assessmentResults = AssessmentResult::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(80, $assessmentResults->count());

        // 9. Payments / Invoices
        $payments = Payment::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(150, $payments->count());

        // Status variety
        $this->assertTrue($payments->contains('status', 'lunas'));
        $this->assertTrue($payments->contains('status', 'belum_bayar'));
        $this->assertTrue($payments->contains('status', 'terlambat'));
        $this->assertTrue($payments->contains('status', 'menunggu_verifikasi'));

        // 10. Honor Schemes & Calculations
        $schemes = HonorScheme::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(4, $schemes->count());

        $honorCalculations = HonorCalculation::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(20, $honorCalculations->count());

        // 11. Audit Logs
        $auditLogs = AuditLog::where('tenant_id', $tenant->id)->get();
        $this->assertGreaterThanOrEqual(5, $auditLogs->count());
    }

    public function test_demo_data_seeder_is_idempotent(): void
    {
        // Run once
        $this->seed(DemoDataSeeder::class);
        $studentCount1 = Student::count();
        $paymentCount1 = Payment::count();
        $sessionCount1 = TeachingSession::count();

        // Run again
        $this->seed(DemoDataSeeder::class);
        $studentCount2 = Student::count();
        $paymentCount2 = Payment::count();
        $sessionCount2 = TeachingSession::count();

        // Must not duplicate data
        $this->assertEquals($studentCount1, $studentCount2);
        $this->assertEquals($paymentCount1, $paymentCount2);
        $this->assertEquals($sessionCount1, $sessionCount2);
    }
}
