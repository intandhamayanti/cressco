<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchUser;
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
use App\Models\TutorAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportsAndDashboardDataIntegrationTest extends TestCase
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

    protected User $tutorOtherTenant;

    protected Classes $classBandung;

    protected Classes $classSemarang;

    protected Student $student1;

    protected Student $student2;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->classSemarang = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSemarang->id,
            'name' => 'Fisika UTBK Semarang',
            'capacity' => 20,
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
            'name' => 'Siswa 1',
            'parent_name' => 'Ortu 1',
            'status' => 'active',
        ]);

        $this->student2 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSemarang->id,
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
            'branch_id' => $this->branchSemarang->id,
            'student_id' => $this->student2->id,
            'class_id' => $this->classSemarang->id,
            'started_at' => now()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);
    }

    public function test_owner_dashboard_shows_accurate_actual_kpis_and_branch_scoping(): void
    {
        $this->actingAs($this->owner);

        $now = Carbon::now();
        $thisMonthPeriod = $now->format('Y-m');

        // 1. Create verified/paid payment for Bandung: Rp 1.500.000
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'student_id' => $this->student1->id,
            'period' => $thisMonthPeriod,
            'amount' => 1500000,
            'due_date' => $now->toDateString(),
            'paid_at' => $now,
            'status' => 'lunas',
            'recorded_by' => $this->owner->id,
        ]);

        // 2. Create pending payment for Bandung: Rp 500.000
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'student_id' => $this->student1->id,
            'period' => $thisMonthPeriod,
            'amount' => 500000,
            'due_date' => $now->toDateString(),
            'status' => 'belum_bayar',
            'recorded_by' => $this->owner->id,
        ]);

        // 3. Create verified payment for Semarang: Rp 2.000.000
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSemarang->id,
            'student_id' => $this->student2->id,
            'period' => $thisMonthPeriod,
            'amount' => 2000000,
            'due_date' => $now->toDateString(),
            'paid_at' => $now,
            'status' => 'lunas',
            'recorded_by' => $this->owner->id,
        ]);

        // 4. Create Honor Scheme & Finalized Honor Calculation: Rp 400.000
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Default',
            'method' => 'per_session',
            'rate' => 100000,
            'effective_from' => $now->startOfMonth()->toDateString(),
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'tutor_id' => $this->tutorA->id,
            'honor_scheme_id' => $scheme->id,
            'period_start' => $now->startOfMonth()->toDateString(),
            'period_end' => $now->endOfMonth()->toDateString(),
            'method' => 'per_session',
            'base_amount' => 400000,
            'final_amount' => 400000,
            'status' => 'final',
            'calculated_by' => $this->owner->id,
            'finalized_by' => $this->owner->id,
            'finalized_at' => $now,
        ]);

        // Consolidated Dashboard View
        $response = $this->get(route('owner.dashboard', ['period' => 'this_month']));
        $response->assertOk();
        $response->assertViewHas('revenue', 3500000.0); // 1.5M + 2.0M
        $response->assertViewHas('outstanding', 500000.0);
        $response->assertViewHas('expenses', 400000.0);
        $response->assertViewHas('estimatedProfit', 3100000.0); // 3.5M - 400K
        $response->assertViewHas('totalStudents', 2);

        // Branch-scoped Dashboard View (Bandung only)
        $scopedResponse = $this->get(route('owner.dashboard', [
            'period' => 'this_month',
            'branch_id' => $this->branchBandung->id,
        ]));
        $scopedResponse->assertOk();
        $scopedResponse->assertViewHas('revenue', 1500000.0);
        $scopedResponse->assertViewHas('outstanding', 500000.0);
        $scopedResponse->assertViewHas('expenses', 400000.0);
        $scopedResponse->assertViewHas('estimatedProfit', 1100000.0);
        $scopedResponse->assertViewHas('totalStudents', 1);
    }

    public function test_admin_dashboard_strictly_scoped_to_accessible_branch(): void
    {
        $this->actingAs($this->adminBandung);

        $now = Carbon::now();

        // Teaching session in Bandung today
        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'class_id' => $this->classBandung->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => $now->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'status' => 'completed',
        ]);

        // Teaching session in Semarang today (inaccessible to Admin Bandung)
        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSemarang->id,
            'class_id' => $this->classSemarang->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => $now->toDateString(),
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'status' => 'completed',
        ]);

        $response = $this->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertViewHas('activeStudentsCount', 1); // Only Student 1 in Bandung
        $response->assertViewHas('activeClassesCount', 1); // Only Kimia Bandung
        $response->assertViewHas('todaySessionsCount', 1); // Only Bandung session
        $response->assertViewHas('todayCompletedSessionsCount', 1);
    }

    public function test_tutor_dashboard_data_reflects_actual_teaching_and_schedules(): void
    {
        $this->actingAs($this->tutorA);

        $now = Carbon::now();

        // 1. Create schedule for class Kimia Bandung
        Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'class_id' => $this->classBandung->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => $now->dayOfWeek,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'starts_on' => $now->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        // 2. Completed session where Tutor A was the actual tutor
        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'class_id' => $this->classBandung->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => $now->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'completed',
        ]);

        $response = $this->get(route('tutor.dashboard'));
        $response->assertOk();
        $response->assertViewHas('totalClassesCount', 1);
        $response->assertViewHas('completedSessionsCount', 1);
        $response->assertViewHas('weeklySchedulesCount', 1);
    }

    public function test_owner_reports_with_period_and_branch_filters(): void
    {
        $this->actingAs($this->owner);

        $period = '2026-10';

        // Payment Bandung Lunas: Rp 1.000.000
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'student_id' => $this->student1->id,
            'period' => $period,
            'amount' => 1000000,
            'due_date' => '2026-10-15',
            'paid_at' => '2026-10-10 10:00:00',
            'status' => 'lunas',
            'recorded_by' => $this->owner->id,
        ]);

        // Payment Semarang Overdue: Rp 750.000
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchSemarang->id,
            'student_id' => $this->student2->id,
            'period' => $period,
            'amount' => 750000,
            'due_date' => '2026-10-05',
            'status' => 'terlambat',
            'recorded_by' => $this->owner->id,
        ]);

        // Honor Calculation for 2026-10: Rp 300.000
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

        HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'tutor_id' => $this->tutorA->id,
            'honor_scheme_id' => $scheme->id,
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'method' => 'per_session',
            'base_amount' => 300000,
            'final_amount' => 300000,
            'status' => 'paid',
            'calculated_by' => $this->owner->id,
            'finalized_by' => $this->owner->id,
            'paid_at' => '2026-10-31 17:00:00',
        ]);

        // 1. Consolidated Report for 2026-10
        $response = $this->get(route('owner.reports.index', ['period' => $period]));
        $response->assertOk();
        $metrics = $response->viewData('metrics');
        $this->assertEquals(1750000, $metrics['totalInvoiced']);
        $this->assertEquals(1000000, $metrics['totalRevenue']);
        $this->assertEquals(750000, $metrics['totalOutstanding']);
        $this->assertEquals(750000, $metrics['totalOverdue']);
        $this->assertEquals(300000, $metrics['totalExpenses']);
        $this->assertEquals(700000, $metrics['netProfit']); // 1.0M - 300K

        // 2. Branch-filtered Report for Bandung
        $scopedResponse = $this->get(route('owner.reports.index', [
            'period' => $period,
            'branch_id' => $this->branchBandung->id,
        ]));
        $scopedResponse->assertOk();
        $scopedMetrics = $scopedResponse->viewData('metrics');
        $this->assertEquals(1000000, $scopedMetrics['totalInvoiced']);
        $this->assertEquals(1000000, $scopedMetrics['totalRevenue']);
        $this->assertEquals(0, $scopedMetrics['totalOutstanding']);
        $this->assertEquals(300000, $scopedMetrics['totalExpenses']);
        $this->assertEquals(700000, $scopedMetrics['netProfit']);
    }

    public function test_admin_reports_reflects_actual_attendance_and_finance_in_scope(): void
    {
        $this->actingAs($this->adminBandung);

        $period = '2026-10';

        // Session & Attendance in Bandung
        $session = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'class_id' => $this->classBandung->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => '2026-10-12',
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'completed',
        ]);

        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'teaching_session_id' => $session->id,
            'student_id' => $this->student1->id,
            'status' => 'hadir',
            'recorded_by' => $this->tutorA->id,
            'recorded_at' => '2026-10-12 12:05:00',
        ]);

        // Payment in Bandung
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchBandung->id,
            'student_id' => $this->student1->id,
            'period' => $period,
            'amount' => 800000,
            'due_date' => '2026-10-10',
            'paid_at' => '2026-10-09 14:00:00',
            'status' => 'lunas',
            'recorded_by' => $this->adminBandung->id,
        ]);

        $response = $this->get(route('admin.reports.index', ['period' => $period]));
        $response->assertOk();

        $attendance = $response->viewData('attendance');
        $this->assertEquals(1, $attendance['sessions']);
        $this->assertEquals(1, $attendance['total']);
        $this->assertEquals(1, $attendance['present']);
        $this->assertEquals(100.0, $attendance['rate']);

        $finance = $response->viewData('finance');
        $this->assertEquals(800000, $finance['invoiced']);
        $this->assertEquals(800000, $finance['revenue']);
        $this->assertEquals(0, $finance['outstanding']);
    }

    public function test_tenant_isolation_and_authorization_across_dashboards_and_reports(): void
    {
        // 1. Admin cannot access Owner Dashboard or Owner Reports
        $this->actingAs($this->adminBandung);
        $this->get(route('owner.dashboard'))->assertForbidden();
        $this->get(route('owner.reports.index'))->assertForbidden();

        // 2. Admin cannot access report for unauthorized branch in same tenant
        $this->get(route('admin.reports.index', ['branch_id' => $this->branchSemarang->id]))->assertForbidden();

        // 3. Tutor cannot access Admin or Owner Dashboard/Reports
        $this->actingAs($this->tutorA);
        $this->get(route('owner.dashboard'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('admin.reports.index'))->assertForbidden();

        // 4. Other tenant data is completely isolated
        $this->actingAs($this->owner);
        $response = $this->get(route('owner.dashboard'));
        $response->assertOk();
        // The branches listed must ONLY belong to $this->tenant
        $branchesInView = $response->viewData('branches');
        $this->assertFalse($branchesInView->contains('id', $this->branchOtherTenant->id));
    }
}
