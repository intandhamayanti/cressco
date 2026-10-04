<?php

namespace Tests\Feature;

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
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use App\Services\HonorCalculationService;
use App\Services\PaymentReminderService;
use App\Services\TeachingSessionGenerationService;
use App\Services\TutorReplacementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CrossModuleIntegrationEndToEndTest extends TestCase
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

    protected TeachingSessionGenerationService $scheduleService;

    protected TutorReplacementService $replacementService;

    protected HonorCalculationService $honorService;

    protected PaymentReminderService $reminderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scheduleService = app(TeachingSessionGenerationService::class);
        $this->replacementService = app(TutorReplacementService::class);
        $this->honorService = app(HonorCalculationService::class);
        $this->reminderService = app(PaymentReminderService::class);

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Akademi Cendekia Mulia',
            'slug' => 'cendekia-mulia',
            'status' => 'active',
        ]);

        $this->otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Other Center',
            'slug' => 'other-center',
            'status' => 'active',
        ]);

        $this->branchBandung = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Bandung Riau',
            'code' => 'BDG-01',
            'city' => 'Bandung',
            'status' => 'active',
        ]);

        $this->branchSemarang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Semarang Simpang',
            'code' => 'SMG-01',
            'city' => 'Semarang',
            'status' => 'active',
        ]);

        $this->branchOtherTenant = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Cabang Jogja',
            'code' => 'JOG-01',
            'city' => 'Yogyakarta',
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

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'tutor_id' => $this->tutorA->id,
            'class_id' => $this->classBandung->id,
            'status' => 'active',
        ]);

        $this->student1 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'name' => 'Andi Wijaya',
            'parent_name' => 'Bpk. Wijaya',
            'parent_phone' => '081234567890',
            'status' => 'active',
        ]);

        $this->student2 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'name' => 'Citra Lestari',
            'parent_name' => 'Ibu Lestari',
            'parent_phone' => '081298765432',
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'student_id' => $this->student1->id,
            'class_id' => $this->classBandung->id,
            'started_at' => '2026-10-01',
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'student_id' => $this->student2->id,
            'class_id' => $this->classBandung->id,
            'started_at' => '2026-10-01',
            'status' => 'active',
        ]);
    }

    /**
     * E2E Flow 1: Schedule -> Session -> Replacement -> Attendance -> Honor Calculation
     */
    public function test_end_to_end_teaching_session_to_honor_calculation_workflow(): void
    {
        // 1. Configure default honor scheme: Rp 150.000 per session
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Reguler Bandung',
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

        // 2. Create recurring schedule for Monday (day_of_week = 1)
        $schedule = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'class_id' => $this->classBandung->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 1, // Monday
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'starts_on' => '2026-10-01',
            'ends_on' => '2026-10-31',
            'status' => 'active',
        ]);

        // 3. Generate teaching sessions from recurring schedule
        $genResult = $this->scheduleService->generateForSchedule($schedule, '2026-10-01', '2026-10-31');
        $generatedSessions = $genResult['sessions'];
        // Oct 2026 Mondays are: 5, 12, 19, 26 (4 Mondays)
        $this->assertCount(4, $generatedSessions);

        $session1 = $generatedSessions->first(fn ($s) => Carbon::parse($s->session_date)->toDateString() === '2026-10-05');
        $session2 = $generatedSessions->first(fn ($s) => Carbon::parse($s->session_date)->toDateString() === '2026-10-12');
        $session3 = $generatedSessions->first(fn ($s) => Carbon::parse($s->session_date)->toDateString() === '2026-10-19');
        $session4 = $generatedSessions->first(fn ($s) => Carbon::parse($s->session_date)->toDateString() === '2026-10-26');

        // 4. Sesi 1 (2026-10-05): Tutor A teaches normally
        $this->actingAs($this->tutorA);
        $this->post(route('tutor.sessions.attendances.store', $session1), [
            'attendances' => [
                ['student_id' => $this->student1->id, 'status' => 'hadir'],
                ['student_id' => $this->student2->id, 'status' => 'hadir'],
            ],
            'material' => 'Termokimia Part 1',
            'mark_session_completed' => true,
        ])->assertRedirect();
        $session1->refresh();
        $this->assertEquals('completed', $session1->status);
        $this->assertEquals($this->tutorA->id, $session1->actual_tutor_id);

        // 5. Sesi 2 (2026-10-12): Tutor A is replaced by Tutor B
        $this->actingAs($this->adminBandung);
        $this->replacementService->replaceTutor($session2, $this->tutorB, 'Tutor A sakit', $this->adminBandung);
        $session2->refresh();
        $this->assertEquals($this->tutorB->id, $session2->actual_tutor_id);

        // Tutor B records attendance and completes session 2
        $this->actingAs($this->tutorB);
        $this->post(route('tutor.sessions.attendances.store', $session2), [
            'attendances' => [
                ['student_id' => $this->student1->id, 'status' => 'hadir'],
                ['student_id' => $this->student2->id, 'status' => 'izin', 'note' => 'Izin acara keluarga'],
            ],
            'material' => 'Termokimia Part 2',
            'mark_session_completed' => true,
        ])->assertRedirect();
        $session2->refresh();
        $this->assertEquals('completed', $session2->status);

        // 6. Sesi 3 (2026-10-19): Tutor A teaches normally
        $this->actingAs($this->tutorA);
        $this->post(route('tutor.sessions.attendances.store', $session3), [
            'attendances' => [
                ['student_id' => $this->student1->id, 'status' => 'hadir'],
                ['student_id' => $this->student2->id, 'status' => 'hadir'],
            ],
            'material' => 'Laju Reaksi',
            'mark_session_completed' => true,
        ])->assertRedirect();
        $session3->refresh();
        $this->assertEquals('completed', $session3->status);

        // 7. Sesi 4 (2026-10-26): Cancelled due to holiday
        $session4->update(['status' => 'cancelled']);

        // 8. Run Honor Calculation for Period 2026-10
        // Tutor A taught 2 completed sessions -> 2 * 150.000 = Rp 300.000
        $calcA = $this->honorService->calculateForTutor(
            $this->tutorA,
            '2026-10-01',
            '2026-10-31',
            $this->adminBandung,
            $this->branchBandung->id
        );
        $this->assertEquals(300000, (float) $calcA->base_amount);
        $this->assertEquals('draft', $calcA->status);

        // Tutor B taught 1 completed session (replacement) -> 1 * 150.000 = Rp 150.000
        $calcB = $this->honorService->calculateForTutor(
            $this->tutorB,
            '2026-10-01',
            '2026-10-31',
            $this->adminBandung,
            $this->branchBandung->id
        );
        $this->assertEquals(150000, (float) $calcB->base_amount);
        $this->assertEquals('draft', $calcB->status);

        // Finalize calculation for Tutor A with bonus adjustment of 50.000
        $finalizedA = $this->honorService->finalizeCalculation($calcA, $this->adminBandung, 50000, 'Bonus materi lengkap');
        $this->assertEquals(350000, (float) $finalizedA->final_amount);
        $this->assertEquals('final', $finalizedA->status);

        // Mark paid for Tutor A
        $paidA = $this->honorService->markPaid($finalizedA);
        $this->assertEquals('paid', $paidA->status);
        $this->assertNotNull($paidA->paid_at);
    }

    /**
     * E2E Flow 2: Payment -> Reminder -> Reports & Dashboard
     */
    public function test_end_to_end_payment_reminder_to_reports_dashboard_workflow(): void
    {
        $period = '2026-10';

        // 1. Create unpaid invoice for Student 1: Rp 1.200.000
        $payment1 = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'student_id' => $this->student1->id,
            'period' => $period,
            'amount' => 1200000,
            'due_date' => '2026-10-10',
            'status' => 'belum_bayar',
            'recorded_by' => $this->adminBandung->id,
        ]);

        // 2. Generate Reminder for Student 1
        $reminder = $this->reminderService->generateReminder($payment1);
        $this->assertStringContainsString('Andi Wijaya', $reminder['text']);
        $this->assertStringContainsString('Bpk. Wijaya', $reminder['text']);
        $this->assertStringContainsString('1.200.000', $reminder['text']);
        $this->assertEquals('081234567890', $reminder['parent_phone']);

        // 3. Parent makes payment -> Admin verifies payment
        $this->actingAs($this->adminBandung);
        $this->patch(route('admin.payments.verify', $payment1))->assertRedirect();
        $payment1->refresh();
        $this->assertEquals('lunas', $payment1->status);
        $this->assertNotNull($payment1->paid_at);

        // 4. Create second payment overdue for Student 2: Rp 800.000
        $payment2 = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'student_id' => $this->student2->id,
            'period' => $period,
            'amount' => 800000,
            'due_date' => '2026-10-05',
            'status' => 'terlambat',
            'recorded_by' => $this->adminBandung->id,
        ]);

        // 5. Create Finalized Honor for Tutor A: Rp 350.000
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Default',
            'method' => 'per_session',
            'rate' => 150000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'tutor_id' => $this->tutorA->id,
            'honor_scheme_id' => $scheme->id,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'method' => 'per_session',
            'base_amount' => 350000,
            'final_amount' => 350000,
            'status' => 'final',
            'calculated_by' => $this->adminBandung->id,
            'finalized_by' => $this->adminBandung->id,
            'finalized_at' => now(),
        ]);

        // 6. Verify Dashboard Metrics reflect exact values
        $this->actingAs($this->owner);
        $dashResponse = $this->get(route('owner.dashboard', ['period' => 'this_month']));
        $dashResponse->assertOk();
        $dashResponse->assertViewHas('revenue', 1200000.0);
        $dashResponse->assertViewHas('outstanding', 800000.0);
        $dashResponse->assertViewHas('expenses', 350000.0);
        $dashResponse->assertViewHas('estimatedProfit', 850000.0); // 1.2M - 350K

        // 7. Verify Reports reflect exact values
        $repResponse = $this->get(route('owner.reports.index', ['period' => $period]));
        $repResponse->assertOk();
        $metrics = $repResponse->viewData('metrics');
        $this->assertEquals(2000000, $metrics['totalInvoiced']); // 1.2M + 800K
        $this->assertEquals(1200000, $metrics['totalRevenue']);
        $this->assertEquals(800000, $metrics['totalOutstanding']);
        $this->assertEquals(350000, $metrics['totalExpenses']);
        $this->assertEquals(850000, $metrics['netProfit']);
    }

    /**
     * Cross-tenant and branch scope enforcement across all modules
     */
    public function test_cross_tenant_and_cross_branch_scope_integrity(): void
    {
        // 1. Other tenant payment & calculation
        $otherScheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Skema Luar',
            'method' => 'per_session',
            'rate' => 200000,
            'effective_from' => '2026-10-01',
            'status' => 'active',
            'created_by' => $this->tutorOtherTenant->id,
        ]);

        $otherCalc = HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'tutor_id' => $this->tutorOtherTenant->id,
            'honor_scheme_id' => $otherScheme->id,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'method' => 'per_session',
            'base_amount' => 600000,
            'final_amount' => 600000,
            'status' => 'final',
            'calculated_by' => $this->tutorOtherTenant->id,
        ]);

        // Owner of Tenant 1 must never see Tenant 2's calculations
        $this->actingAs($this->owner);
        $this->get(route('owner.honors.show', $otherCalc))->assertNotFound();

        // Admin Bandung cannot trigger honor calculation or replacement in Semarang
        $this->actingAs($this->adminBandung);
        $this->post(route('admin.honors.calculate'), [
            'tutor_id' => $this->tutorA->id,
            'branch_id' => $this->branchSemarang->id,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
        ])->assertForbidden();
    }
}
