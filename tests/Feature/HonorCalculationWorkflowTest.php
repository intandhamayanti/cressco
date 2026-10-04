<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\HonorAssignment;
use App\Models\HonorCalculation;
use App\Models\HonorScheme;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\User;
use App\Services\HonorCalculationService;
use App\Services\TutorReplacementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HonorCalculationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Tenant $otherTenant;

    protected Branch $branchBandung;

    protected Branch $branchSemarang;

    protected Branch $branchOtherTenant;

    protected User $owner;

    protected User $adminBandung;

    protected User $tutorA;

    protected User $tutorB;

    protected User $tutorOtherTenant;

    protected Classes $classBandung;

    protected Student $student1;

    protected Student $student2;

    protected HonorCalculationService $honorService;

    protected TutorReplacementService $replacementService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->honorService = app(HonorCalculationService::class);
        $this->replacementService = app(TutorReplacementService::class);

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Akademi Cendekia Mulia',
            'slug' => 'cendekia-mulia',
            'status' => 'active',
        ]);

        $this->otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Other Tutoring Center',
            'slug' => 'other-center',
            'status' => 'active',
        ]);

        $this->branchBandung = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Bandung Riau',
            'code' => 'BDG-01',
            'status' => 'active',
        ]);

        $this->branchSemarang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Semarang Simpang',
            'code' => 'SMG-01',
            'status' => 'active',
        ]);

        $this->branchOtherTenant = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Cabang Jogja',
            'code' => 'JOG-01',
            'status' => 'active',
        ]);

        $this->owner = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Bambang Owner',
            'email' => 'bambang.owner@cendekia.test',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->adminBandung = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Bandung',
            'email' => 'admin.bandung@cendekia.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->adminBandung->id,
            'branch_id' => $this->branchBandung->id,
        ]);

        $this->tutorA = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor Asep',
            'email' => 'asep.tutor@cendekia.test',
            'password' => bcrypt('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->tutorA->id,
            'branch_id' => $this->branchBandung->id,
        ]);

        $this->tutorB = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor Budi Pengganti',
            'email' => 'budi.tutor@cendekia.test',
            'password' => bcrypt('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->tutorB->id,
            'branch_id' => $this->branchBandung->id,
        ]);

        $this->tutorOtherTenant = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Tutor Luar',
            'email' => 'tutor.luar@other.test',
            'password' => bcrypt('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->classBandung = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'name' => 'Kimia UTBK Bandung',
            'capacity' => 15,
            'status' => 'active',
        ]);

        $this->student1 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'name' => 'Siswa 1',
            'parent_name' => 'Ortu 1',
            'status' => 'active',
        ]);

        $this->student2 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'name' => 'Siswa 2',
            'parent_name' => 'Ortu 2',
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'student_id' => $this->student1->id,
            'class_id' => $this->classBandung->id,
            'started_at' => now()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'student_id' => $this->student2->id,
            'class_id' => $this->classBandung->id,
            'started_at' => now()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);
    }

    public function test_per_session_honor_calculation(): void
    {
        // 1. Create per_session scheme (Rate: Rp 100.000 / session)
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Per Sesi Reguler',
            'method' => 'per_session',
            'rate' => 100000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'assignment_type' => 'default',
            'honor_scheme_id' => $scheme->id,
            'effective_from' => '2026-10-01',
        ]);

        // 2. Create 3 completed sessions for Tutor A
        for ($i = 1; $i <= 3; $i++) {
            TeachingSession::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $this->tenant->id,
                'branch_id' => $this->branchBandung->id,
                'class_id' => $this->classBandung->id,
                'scheduled_tutor_id' => $this->tutorA->id,
                'actual_tutor_id' => $this->tutorA->id,
                'session_date' => "2026-10-0{$i}",
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'status' => 'completed',
            ]);
        }

        // 1 cancelled session (should NOT be counted)
        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'class_id' => $this->classBandung->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => '2026-10-04',
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'cancelled',
        ]);

        $calculation = $this->honorService->calculateForTutor(
            $this->tutorA,
            '2026-10-01',
            '2026-10-31',
            $this->adminBandung,
            $this->branchBandung->id
        );

        $this->assertEquals(300000, (float) $calculation->base_amount);
        $this->assertEquals(300000, (float) $calculation->final_amount);
        $this->assertEquals('per_session', $calculation->method);
        $this->assertEquals('draft', $calculation->status);
    }

    public function test_per_student_honor_calculation(): void
    {
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Per Siswa Hadir',
            'method' => 'per_student',
            'rate' => 25000, // Rp 25.000 per student present
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        $session = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'class_id' => $this->classBandung->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => '2026-10-05',
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'status' => 'completed',
        ]);

        // Student 1 present (hadir), Student 2 absent (alpa)
        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'teaching_session_id' => $session->id,
            'student_id' => $this->student1->id,
            'status' => 'hadir',
            'recorded_by' => $this->tutorA->id,
            'recorded_at' => now(),
        ]);

        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'teaching_session_id' => $session->id,
            'student_id' => $this->student2->id,
            'status' => 'alpa',
            'recorded_by' => $this->tutorA->id,
            'recorded_at' => now(),
        ]);

        $calculation = $this->honorService->calculateForTutor(
            $this->tutorA,
            '2026-10-01',
            '2026-10-31',
            $this->adminBandung,
            $this->branchBandung->id,
            $scheme
        );

        // 1 present student * 25,000 = 25,000
        $this->assertEquals(25000, (float) $calculation->base_amount);
    }

    public function test_fixed_monthly_honor_calculation(): void
    {
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Gaji Pokok Tutor',
            'method' => 'fixed_monthly',
            'fixed_amount' => 2500000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        $calculation = $this->honorService->calculateForTutor(
            $this->tutorA,
            '2026-10-01',
            '2026-10-31',
            $this->owner,
            null,
            $scheme
        );

        $this->assertEquals(2500000, (float) $calculation->base_amount);
        $this->assertEquals('fixed_monthly', $calculation->method);
    }

    public function test_hybrid_honor_calculation(): void
    {
        // Hybrid: Rp 1.500.000 fixed + Rp 75.000 per session
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Hybrid Base + Per Sesi',
            'method' => 'fixed_monthly',
            'fixed_amount' => 1500000,
            'rate' => 75000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        // 4 completed sessions
        for ($i = 1; $i <= 4; $i++) {
            TeachingSession::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $this->tenant->id,
                'branch_id' => $this->branchBandung->id,
                'class_id' => $this->classBandung->id,
                'scheduled_tutor_id' => $this->tutorA->id,
                'actual_tutor_id' => $this->tutorA->id,
                'session_date' => "2026-10-1{$i}",
                'start_time' => '13:00:00',
                'end_time' => '15:00:00',
                'status' => 'completed',
            ]);
        }

        $calculation = $this->honorService->calculateForTutor(
            $this->tutorA,
            '2026-10-01',
            '2026-10-31',
            $this->owner,
            $this->branchBandung->id,
            $scheme
        );

        // 1,500,000 + (4 * 75,000) = 1,800,000
        $this->assertEquals(1800000, (float) $calculation->base_amount);
    }

    public function test_replacement_tutor_becomes_basis_for_honor_not_scheduled_tutor(): void
    {
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Per Sesi',
            'method' => 'per_session',
            'rate' => 150000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'assignment_type' => 'default',
            'honor_scheme_id' => $scheme->id,
            'effective_from' => '2026-10-01',
        ]);

        // Session was scheduled for Tutor A
        $session = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'class_id' => $this->classBandung->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => '2026-10-20',
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'scheduled',
        ]);

        // Tutor A is sick; Admin assigns Tutor B as replacement
        $this->replacementService->replaceTutor($session, $this->tutorB, 'Tutor A berhalangan hadir (sakit)', $this->adminBandung);

        // Complete the session
        $session->update(['status' => 'completed']);

        // Calculate for Tutor A (scheduled) -> 0 sessions, 0 honor
        $calcA = $this->honorService->calculateForTutor($this->tutorA, '2026-10-01', '2026-10-31', $this->adminBandung, $this->branchBandung->id);
        $this->assertEquals(0, (float) $calcA->base_amount);

        // Calculate for Tutor B (actual replacement) -> 1 session, 150,000 honor
        $calcB = $this->honorService->calculateForTutor($this->tutorB, '2026-10-01', '2026-10-31', $this->adminBandung, $this->branchBandung->id);
        $this->assertEquals(150000, (float) $calcB->base_amount);
    }

    public function test_duplicate_calculation_prevention_and_status_locking(): void
    {
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Default',
            'method' => 'per_session',
            'rate' => 100000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'assignment_type' => 'default',
            'honor_scheme_id' => $scheme->id,
            'effective_from' => '2026-10-01',
        ]);

        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'class_id' => $this->classBandung->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => '2026-10-22',
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'completed',
        ]);

        // Run calculation 1st time
        $calc1 = $this->honorService->calculateForTutor($this->tutorA, '2026-10-01', '2026-10-31', $this->adminBandung, $this->branchBandung->id);

        // Run calculation 2nd time
        $calc2 = $this->honorService->calculateForTutor($this->tutorA, '2026-10-01', '2026-10-31', $this->adminBandung, $this->branchBandung->id);

        // Must be same record id, not duplicated
        $this->assertEquals($calc1->id, $calc2->id);
        $this->assertEquals(1, HonorCalculation::where('tenant_id', $this->tenant->id)->where('tutor_id', $this->tutorA->id)->count());

        // Finalize calculation with bonus adjustment
        $finalized = $this->honorService->finalizeCalculation($calc1, $this->adminBandung, 50000, 'Bonus ketepatan waktu');
        $this->assertEquals('final', $finalized->status);
        $this->assertEquals(150000, (float) $finalized->final_amount);
        $this->assertNotNull($finalized->finalized_at);

        // Marking as paid
        $paid = $this->honorService->markPaid($finalized);
        $this->assertEquals('paid', $paid->status);
        $this->assertNotNull($paid->paid_at);
    }

    public function test_controller_endpoints_and_branch_scoping(): void
    {
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Default Admin',
            'method' => 'per_session',
            'rate' => 120000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'assignment_type' => 'default',
            'honor_scheme_id' => $scheme->id,
            'effective_from' => '2026-10-01',
        ]);

        $this->actingAs($this->adminBandung);

        // 1. Admin can trigger calculation in accessible branch
        $response = $this->post(route('admin.honors.calculate'), [
            'tutor_id' => $this->tutorA->id,
            'branch_id' => $this->branchBandung->id,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
        ]);

        $response->assertRedirect(route('admin.honors.index'));
        $this->assertDatabaseHas('honor_calculations', [
            'tenant_id' => $this->tenant->id,
            'tutor_id' => $this->tutorA->id,
            'branch_id' => $this->branchBandung->id,
            'status' => 'draft',
        ]);

        // 2. Admin cannot trigger calculation in unauthorized branch
        $badResponse = $this->post(route('admin.honors.calculate'), [
            'tutor_id' => $this->tutorA->id,
            'branch_id' => $this->branchSemarang->id,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
        ]);
        $badResponse->assertForbidden();
    }

    public function test_effective_honor_scheme_priority_resolution(): void
    {
        // 1. General active scheme
        $generalScheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'General Scheme',
            'method' => 'per_session',
            'rate' => 50000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        // 2. Default tenant assignment scheme
        $defaultScheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Default Scheme',
            'method' => 'per_session',
            'rate' => 75000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'assignment_type' => 'default',
            'honor_scheme_id' => $defaultScheme->id,
            'effective_from' => '2026-10-01',
        ]);

        // Tutor B without override should get defaultScheme
        $resolvedB = $this->honorService->resolveEffectiveScheme($this->tutorB, '2026-10-01', '2026-10-31');
        $this->assertEquals($defaultScheme->id, $resolvedB->id);

        // 3. Tutor override scheme for Tutor A
        $overrideScheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Override Scheme Asep',
            'method' => 'per_session',
            'rate' => 120000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'assignment_type' => 'tutor_override',
            'tutor_id' => $this->tutorA->id,
            'honor_scheme_id' => $overrideScheme->id,
            'effective_from' => '2026-10-01',
        ]);

        // Tutor A with override should get overrideScheme
        $resolvedA = $this->honorService->resolveEffectiveScheme($this->tutorA, '2026-10-01', '2026-10-31');
        $this->assertEquals($overrideScheme->id, $resolvedA->id);
    }

    public function test_batch_calculation_for_tenant(): void
    {
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Default Scheme',
            'method' => 'per_session',
            'rate' => 80000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'assignment_type' => 'default',
            'honor_scheme_id' => $scheme->id,
            'effective_from' => '2026-10-01',
        ]);

        $calculations = $this->honorService->calculateForTenant(
            $this->tenant->id,
            '2026-10-01',
            '2026-10-31',
            $this->owner
        );

        // Should calculate for Tutor A and Tutor B (active tutors in this tenant)
        $this->assertCount(2, $calculations);
        $tutorIds = $calculations->pluck('tutor_id')->all();
        $this->assertContains($this->tutorA->id, $tutorIds);
        $this->assertContains($this->tutorB->id, $tutorIds);
    }

    public function test_tenant_isolation_and_authorization(): void
    {
        $otherScheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Skema Cabang Lain',
            'method' => 'per_session',
            'rate' => 100000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->tutorOtherTenant->id,
        ]);

        $calcOtherTenant = HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'tutor_id' => $this->tutorOtherTenant->id,
            'honor_scheme_id' => $otherScheme->id,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'method' => 'per_session',
            'base_amount' => 500000,
            'final_amount' => 500000,
            'status' => 'draft',
            'calculated_by' => $this->tutorOtherTenant->id,
        ]);

        // Admin cannot access other tenant calculation
        $this->actingAs($this->adminBandung);
        $this->get(route('admin.honors.show', $calcOtherTenant))->assertNotFound();

        // Tutor cannot calculate or manage honors
        $this->actingAs($this->tutorA);
        $this->get(route('admin.honors.index'))->assertForbidden();
        $this->post(route('admin.honors.calculate'), [])->assertForbidden();
    }
}
