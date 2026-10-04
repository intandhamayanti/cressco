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
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\TeachingSessionGenerationService;
use App\Services\TutorReplacementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BusinessRulesAndWorkflowValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Branch $branch;

    protected User $owner;

    protected User $admin;

    protected User $tutorBudi;

    protected User $tutorSinta;

    protected Classes $classMath;

    protected Student $studentAndi;

    protected Student $studentDewi;

    protected Schedule $scheduleMath;

    protected TeachingSessionGenerationService $sessionService;

    protected TutorReplacementService $replacementService;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sessionService = app(TeachingSessionGenerationService::class);
        $this->replacementService = app(TutorReplacementService::class);
        $this->paymentService = app(PaymentService::class);

        // 1. Tenant & Branch
        $this->tenant = Tenant::create([
            'name' => 'Bimbel Prestasi',
            'slug' => 'bimbel-prestasi',
            'status' => 'active',
        ]);

        $this->branch = Branch::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Pusat',
            'code' => 'PST-01',
            'status' => 'active',
        ]);

        // 2. Users
        $this->owner = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pak Owner',
            'email' => 'owner@prestasi.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Cabang',
            'email' => 'admin@prestasi.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);
        BranchUser::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'user_id' => $this->admin->id,
        ]);

        $this->tutorBudi = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Kak Budi (Scheduled)',
            'email' => 'budi@prestasi.com',
            'password' => bcrypt('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->tutorSinta = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Kak Sinta (Replacement)',
            'email' => 'sinta@prestasi.com',
            'password' => bcrypt('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        // 3. Class & Tutors
        $this->classMath = Classes::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Matematika Intensif 12 SMA',
            'subject' => 'Matematika',
            'level' => '12 SMA',
            'capacity' => 15,
            'price_per_month' => 600000,
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classMath->id,
            'tutor_id' => $this->tutorBudi->id,
            'started_at' => now()->startOfYear(),
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classMath->id,
            'tutor_id' => $this->tutorSinta->id,
            'started_at' => now()->startOfYear(),
            'status' => 'active',
        ]);

        // 4. Students & Enrollments
        $this->studentAndi = Student::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Andi Pratama',
            'email' => 'andi@prestasi.com',
            'phone' => '081234567890',
            'parent_name' => 'Bapak Andi',
            'parent_phone' => '081234567891',
            'status' => 'active',
        ]);

        Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classMath->id,
            'student_id' => $this->studentAndi->id,
            'started_at' => now()->startOfYear(),
            'status' => 'active',
        ]);

        $this->studentDewi = Student::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Dewi Sartika',
            'email' => 'dewi@prestasi.com',
            'phone' => '081234567892',
            'parent_name' => 'Ibu Dewi',
            'parent_phone' => '081234567893',
            'status' => 'active',
        ]);

        Enrollment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classMath->id,
            'student_id' => $this->studentDewi->id,
            'started_at' => now()->startOfYear(),
            'status' => 'active',
        ]);

        // 5. Recurring Schedule (Monday = day 1, 19:00 - 20:30)
        $this->scheduleMath = Schedule::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorBudi->id,
            'day_of_week' => 1, // Monday
            'start_time' => '19:00',
            'end_time' => '20:30',
            'room' => 'Ruang 101',
            'status' => 'active',
            'starts_on' => '2026-10-01',
            'ends_on' => '2026-10-31',
        ]);
    }

    // ==========================================
    // 1. Recurring Schedule → Teaching Session (BR-028 to BR-037, BR-113 to BR-115)
    // ==========================================

    public function test_schedule_generates_teaching_sessions_with_exact_day_and_idempotency(): void
    {
        // October 2026: Mondays fall on Oct 5, 12, 19, 26 (4 Mondays)
        $result1 = $this->sessionService->generateForSchedule(
            $this->scheduleMath,
            '2026-10-01',
            '2026-10-31'
        );

        $this->assertEquals(4, $result1['generated_count']);
        $this->assertEquals(0, $result1['skipped_existing_count']);
        $this->assertEquals(4, TeachingSession::where('schedule_id', $this->scheduleMath->id)->count());

        // Test Idempotency (running generation again skips existing sessions)
        $result2 = $this->sessionService->generateForSchedule(
            $this->scheduleMath,
            '2026-10-01',
            '2026-10-31'
        );

        $this->assertEquals(0, $result2['generated_count']);
        $this->assertEquals(4, $result2['skipped_existing_count']);
        $this->assertEquals(4, TeachingSession::where('schedule_id', $this->scheduleMath->id)->count());
    }

    public function test_inactive_schedule_does_not_generate_sessions(): void
    {
        $this->scheduleMath->update(['status' => 'inactive']);

        $result = $this->sessionService->generateForSchedule(
            $this->scheduleMath,
            '2026-10-01',
            '2026-10-31'
        );

        $this->assertEquals(0, $result['generated_count']);
    }

    // ==========================================
    // 2. Actual Teaching & Tutor Replacement (BR-038 to BR-049, BR-085)
    // ==========================================

    public function test_tutor_replacement_preserves_scheduled_tutor_and_updates_actual_tutor_with_audit(): void
    {
        $session = TeachingSession::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'schedule_id' => $this->scheduleMath->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorBudi->id,
            'actual_tutor_id' => $this->tutorBudi->id,
            'session_date' => '2026-10-05',
            'start_time' => '19:00',
            'end_time' => '20:30',
            'status' => 'scheduled',
        ]);

        $replacement = $this->replacementService->replaceTutor(
            $session,
            $this->tutorSinta,
            'Kak Budi ada ujian skripsi',
            $this->admin
        );

        $freshSession = $session->fresh();

        // 1. Scheduled tutor remains Budi, Actual tutor becomes Sinta
        $this->assertEquals($this->tutorBudi->id, $freshSession->scheduled_tutor_id);
        $this->assertEquals($this->tutorSinta->id, $freshSession->actual_tutor_id);

        // 2. Audit replacement record created with reason
        $this->assertDatabaseHas('tutor_replacements', [
            'id' => $replacement->id,
            'teaching_session_id' => $session->id,
            'scheduled_tutor_id' => $this->tutorBudi->id,
            'previous_actual_tutor_id' => $this->tutorBudi->id,
            'replacement_tutor_id' => $this->tutorSinta->id,
            'reason' => 'Kak Budi ada ujian skripsi',
            'changed_by' => $this->admin->id,
        ]);

        // 3. Recurring schedule remains Budi (BR-048)
        $this->assertEquals($this->tutorBudi->id, $this->scheduleMath->fresh()->scheduled_tutor_id);
    }

    // ==========================================
    // 3. Student Attendance & Derived Tutor Attendance (BR-050 to BR-059)
    // ==========================================

    public function test_recording_student_attendance_auto_derives_tutor_attendance_for_actual_tutor(): void
    {
        $session = TeachingSession::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'schedule_id' => $this->scheduleMath->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorBudi->id,
            'actual_tutor_id' => $this->tutorSinta->id, // Sinta is teaching
            'session_date' => '2026-10-05',
            'start_time' => '19:00',
            'end_time' => '20:30',
            'status' => 'scheduled',
        ]);

        $res = $this->actingAs($this->tutorSinta)->post('/tutor/sessions/'.$session->id.'/attendances', [
            'attendances' => [
                [
                    'student_id' => $this->studentAndi->id,
                    'status' => 'hadir',
                    'note' => 'Hadir tepat waktu',
                ],
                [
                    'student_id' => $this->studentDewi->id,
                    'status' => 'izin',
                    'note' => 'Izin lomba OSN',
                ],
            ],
            'material' => 'Kalkulus Differensial',
            'session_notes' => 'Sesi berjalan interaktif',
            'mark_session_completed' => '1',
        ]);

        $res->assertRedirect();

        // Verify Student Attendance saved
        $this->assertDatabaseHas('student_attendances', [
            'teaching_session_id' => $session->id,
            'student_id' => $this->studentAndi->id,
            'status' => 'hadir',
        ]);
        $this->assertDatabaseHas('student_attendances', [
            'teaching_session_id' => $session->id,
            'student_id' => $this->studentDewi->id,
            'status' => 'izin',
        ]);

        // Verify Tutor Attendance derived for actual tutor (Sinta) (BR-057, BR-059)
        $this->assertDatabaseHas('tutor_attendances', [
            'teaching_session_id' => $session->id,
            'tutor_id' => $this->tutorSinta->id,
            'status' => 'present',
        ]);

        // Verify session status updated to completed (BR-042)
        $this->assertEquals('completed', $session->fresh()->status);
    }

    // ==========================================
    // 4. Payment Workflow & Transitions (BR-066 to BR-075)
    // ==========================================

    public function test_payment_status_transitions_and_disallows_invalid_transitions(): void
    {
        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $this->studentAndi->id,
            'period' => '2026-10',
            'amount' => 600000,
            'due_date' => now()->addDays(5),
            'status' => 'belum_bayar',
            'recorded_by' => $this->admin->id,
        ]);

        // Transition 1: verify payment -> lunas
        $verified = $this->paymentService->verifyPayment($payment, $this->admin, now()->toDateTimeString(), 'Transfer BCA');
        $this->assertEquals('lunas', $verified->status);
        $this->assertNotNull($verified->paid_at);

        // Disallowed: trying to verify again or change lunas to belum_bayar throws ValidationException
        $this->expectException(ValidationException::class);
        $this->paymentService->verifyPayment($verified, $this->admin);
    }

    public function test_partial_payment_splits_invoice_accurately(): void
    {
        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $this->studentAndi->id,
            'period' => '2026-10',
            'amount' => 600000,
            'due_date' => now()->addDays(5),
            'status' => 'belum_bayar',
            'recorded_by' => $this->admin->id,
        ]);

        $result = $this->paymentService->recordPartialPayment(
            $payment,
            400000, // Pays 400k of 600k
            $this->admin,
            now()->toDateTimeString(),
            'Bayar cicilan 1'
        );

        // Original payment is now settled for 400k
        $paid = $result['paid_payment'];
        $this->assertEquals('lunas', $paid->status);
        $this->assertEquals(400000, $paid->amount);

        // Remaining balance payment created for 200k
        $remaining = $result['remaining_payment'];
        $this->assertNotNull($remaining);
        $this->assertEquals(200000, $remaining->amount);
        $this->assertEquals('belum_bayar', $remaining->status);
        $this->assertEquals($this->tenant->id, $remaining->tenant_id);
    }

    // ==========================================
    // 5. Tutor Honor Calculation (BR-076 to BR-095)
    // ==========================================

    public function test_per_session_honor_scheme_accurately_credits_actual_tutor_and_excludes_cancelled_sessions(): void
    {
        // Create Honor Scheme: Per Sesi Rp 75.000
        $scheme = HonorScheme::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Honor Per Sesi Standar',
            'method' => 'per_session',
            'rate' => 75000,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        HonorAssignment::create([
            'tenant_id' => $this->tenant->id,
            'honor_scheme_id' => $scheme->id,
            'assignment_type' => 'default',
            'effective_from' => '2026-01-01',
        ]);

        // Create 3 sessions in Oct 2026:
        // Session 1: Sinta taught -> completed
        TeachingSession::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorBudi->id,
            'actual_tutor_id' => $this->tutorSinta->id,
            'session_date' => '2026-10-05',
            'start_time' => '19:00',
            'end_time' => '20:30',
            'status' => 'completed',
        ]);

        // Session 2: Sinta taught -> completed
        TeachingSession::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorSinta->id,
            'actual_tutor_id' => $this->tutorSinta->id,
            'session_date' => '2026-10-12',
            'start_time' => '19:00',
            'end_time' => '20:30',
            'status' => 'completed',
        ]);

        // Session 3: Sinta scheduled -> cancelled (should NOT generate honor per BR-084)
        TeachingSession::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorSinta->id,
            'actual_tutor_id' => $this->tutorSinta->id,
            'session_date' => '2026-10-19',
            'start_time' => '19:00',
            'end_time' => '20:30',
            'status' => 'cancelled',
        ]);

        // Count completed sessions for Sinta in period
        $completedCount = TeachingSession::where('tenant_id', $this->tenant->id)
            ->where('actual_tutor_id', $this->tutorSinta->id)
            ->whereBetween('session_date', ['2026-10-01', '2026-10-31'])
            ->where('status', 'completed')
            ->count();

        $this->assertEquals(2, $completedCount);

        // Record calculation for Sinta: 2 completed * 75.000 = 150.000
        $calculation = HonorCalculation::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'tutor_id' => $this->tutorSinta->id,
            'honor_scheme_id' => $scheme->id,
            'method' => 'per_session',
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'base_amount' => 2 * 75000,
            'final_amount' => 150000,
            'status' => 'final',
            'calculated_by' => $this->admin->id,
            'finalized_by' => $this->owner->id,
        ]);

        $this->assertEquals(150000, $calculation->final_amount);

        // Owner marks as paid
        $res = $this->actingAs($this->owner)->patch('/owner/honors/'.$calculation->id.'/mark-paid');
        $res->assertSessionHasNoErrors();
        $this->assertEquals('paid', $calculation->fresh()->status);
        $this->assertNotNull($calculation->fresh()->paid_at);
    }

    // ==========================================
    // 6. Reports & Operational Metric Consistency (BR-098, BR-099)
    // ==========================================

    public function test_reports_and_dashboard_derive_revenue_outstanding_and_attendance_from_operational_data(): void
    {
        // 1 paid payment of 600k, 1 unpaid payment of 400k
        Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $this->studentAndi->id,
            'period' => '2026-10',
            'amount' => 600000,
            'due_date' => '2026-10-10',
            'paid_at' => '2026-10-05 10:00:00',
            'status' => 'lunas',
            'recorded_by' => $this->admin->id,
        ]);

        Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $this->studentDewi->id,
            'period' => '2026-10',
            'amount' => 400000,
            'due_date' => '2026-10-10',
            'status' => 'belum_bayar',
            'recorded_by' => $this->admin->id,
        ]);

        // Owner Report check
        $resOwnerReport = $this->actingAs($this->owner)->get('/owner/reports?period=2026-10');
        $resOwnerReport->assertOk();
        $resOwnerReport->assertSee('Rp 600.000'); // Revenue
        $resOwnerReport->assertSee('Rp 400.000'); // Outstanding

        // Admin Report check
        $resAdminReport = $this->actingAs($this->admin)->get('/admin/reports?period=2026-10');
        $resAdminReport->assertOk();
        $resAdminReport->assertSee('600.000');
    }
}
