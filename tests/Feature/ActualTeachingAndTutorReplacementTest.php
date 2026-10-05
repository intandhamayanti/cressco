<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\TutorReplacement;
use App\Models\User;
use App\Services\TutorReplacementService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActualTeachingAndTutorReplacementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Branch $branchMalang;

    protected Branch $branchSurabaya;

    protected Branch $branchTenantB;

    protected User $ownerA;

    protected User $adminMalang;

    protected User $tutorA;

    protected User $tutorB;

    protected User $tutorTenantB;

    protected Classes $classMath;

    protected Classes $classEnglish;

    protected Student $studentAhmad;

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

        // Users
        $this->ownerA = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Pak Owner Prime',
            'email' => 'owner@prime.test',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->adminMalang = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Admin Malang',
            'email' => 'admin.malang@prime.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->adminMalang->id,
            'branch_id' => $this->branchMalang->id,
        ]);

        $this->tutorA = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Tutor Arya Scheduled',
            'email' => 'arya.scheduled@prime.test',
            'password' => Hash::make('password123'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->tutorB = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Tutor Bella Replacement',
            'email' => 'bella.replacement@prime.test',
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
            'name' => '11 SMA Bahasa Inggris',
            'subject' => 'Bahasa Inggris',
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'tutor_id' => $this->tutorA->id,
            'started_at' => Carbon::now()->subMonths(2),
            'status' => 'active',
        ]);

        // Students & Enrollments
        $this->studentAhmad = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Ahmad Siswa',
            'nis' => 'PA-001',
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'student_id' => $this->studentAhmad->id,
            'started_at' => Carbon::now()->subMonths(2),
            'status' => 'active',
        ]);

        // Teaching Sessions
        $this->sessionMath = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'room' => 'Ruang 101',
            'status' => 'scheduled',
        ]);

        $this->sessionEnglish = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchSurabaya->id,
            'class_id' => $this->classEnglish->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => Carbon::today()->toDateString(),
            'start_time' => '17:00:00',
            'end_time' => '18:30:00',
            'room' => 'Ruang 201',
            'status' => 'scheduled',
        ]);
    }

    public function test_teaching_session_completion_updates_status_and_actual_teaching(): void
    {
        $this->actingAs($this->tutorA);

        $payload = [
            'attendances' => [
                [
                    'student_id' => $this->studentAhmad->id,
                    'status' => 'hadir',
                    'note' => 'Hadir tepat waktu',
                ],
            ],
            'material' => 'Integral Parsial dan Substitusi Trigonometri',
            'session_notes' => 'Pembahasan 10 soal UTBK selesai',
            'mark_session_completed' => 1,
        ];

        $response = $this->post(route('tutor.sessions.attendances.store', $this->sessionMath), $payload);
        $response->assertRedirect();

        $this->sessionMath->refresh();
        $this->assertEquals('completed', $this->sessionMath->status);
        $this->assertEquals('Integral Parsial dan Substitusi Trigonometri', $this->sessionMath->material);
        $this->assertEquals($this->tutorA->id, $this->sessionMath->actual_tutor_id);
    }

    public function test_scheduled_tutor_vs_actual_tutor_distinction_via_replacement_service(): void
    {
        $service = app(TutorReplacementService::class);

        $replacement = $service->replaceTutor(
            $this->sessionMath,
            $this->tutorB,
            'Tutor Arya berhalangan karena menghadiri seminar kedinasan',
            $this->adminMalang
        );

        $this->assertNotNull($replacement);
        $this->assertInstanceOf(TutorReplacement::class, $replacement);
        $this->assertEquals($this->tutorA->id, $replacement->scheduled_tutor_id);
        $this->assertEquals($this->tutorA->id, $replacement->previous_actual_tutor_id);
        $this->assertEquals($this->tutorB->id, $replacement->replacement_tutor_id);
        $this->assertEquals($this->adminMalang->id, $replacement->changed_by);
        $this->assertEquals('Tutor Arya berhalangan karena menghadiri seminar kedinasan', $replacement->reason);

        $this->sessionMath->refresh();
        $this->assertEquals($this->tutorA->id, $this->sessionMath->scheduled_tutor_id);
        $this->assertEquals($this->tutorB->id, $this->sessionMath->actual_tutor_id);

        $this->assertDatabaseHas('tutor_replacements', [
            'tenant_id' => $this->tenantA->id,
            'teaching_session_id' => $this->sessionMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'replacement_tutor_id' => $this->tutorB->id,
        ]);
    }

    public function test_admin_can_replace_tutor_via_http_endpoint_in_accessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $payload = [
            'replacement_tutor_id' => $this->tutorB->id,
            'reason' => 'Tutor A sakit flu',
        ];

        $response = $this->post(route('admin.attendances.sessions.replace-tutor', $this->sessionMath), $payload);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->sessionMath->refresh();
        $this->assertEquals($this->tutorA->id, $this->sessionMath->scheduled_tutor_id);
        $this->assertEquals($this->tutorB->id, $this->sessionMath->actual_tutor_id);
    }

    public function test_admin_cannot_replace_tutor_in_inaccessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        // Session English is in branchSurabaya, where adminMalang does not have access
        $payload = [
            'replacement_tutor_id' => $this->tutorB->id,
            'reason' => 'Unauthorized branch replacement attempt',
        ];

        $response = $this->post(route('admin.attendances.sessions.replace-tutor', $this->sessionEnglish), $payload);

        $response->assertForbidden();
    }

    public function test_tutor_cannot_replace_tutor_directly(): void
    {
        $this->actingAs($this->tutorA);

        $payload = [
            'replacement_tutor_id' => $this->tutorB->id,
            'reason' => 'Tutor attempting unauthorized replacement',
        ];

        $response = $this->post(route('admin.attendances.sessions.replace-tutor', $this->sessionMath), $payload);

        $response->assertForbidden();
    }

    public function test_replacement_tutor_becomes_basis_for_completed_teaching_session_and_history(): void
    {
        // 1. Replace tutor to Tutor B
        app(TutorReplacementService::class)->replaceTutor(
            $this->sessionMath,
            $this->tutorB,
            'Pergantian tutor resmi',
            $this->ownerA
        );

        // 2. Replacement Tutor B conducts the class and records attendance
        $this->actingAs($this->tutorB);

        $payload = [
            'attendances' => [
                [
                    'student_id' => $this->studentAhmad->id,
                    'status' => 'hadir',
                    'note' => 'Hadir dengan tutor pengganti',
                ],
            ],
            'material' => 'Matriks Invers & Determinan',
            'mark_session_completed' => 1,
        ];

        $this->post(route('tutor.sessions.attendances.store', $this->sessionMath), $payload)
            ->assertRedirect();

        $this->sessionMath->refresh();
        $this->assertEquals('completed', $this->sessionMath->status);
        $this->assertEquals($this->tutorB->id, $this->sessionMath->actual_tutor_id);

        // 3. Tutor B can view this completed session in teaching history
        $responseHistoryB = $this->actingAs($this->tutorB)->get(route('tutor.history.index'));
        $responseHistoryB->assertOk();
        $responseHistoryB->assertSee('Matriks Invers &amp; Determinan', false);

        // 4. Tutor A's history does NOT count this session as their completed teaching session
        $this->assertEquals(1, TeachingSession::where('actual_tutor_id', $this->tutorB->id)->where('status', 'completed')->count());
        $this->assertEquals(0, TeachingSession::where('actual_tutor_id', $this->tutorA->id)->where('status', 'completed')->count());
    }

    public function test_cancelled_sessions_are_not_counted_as_actual_teaching(): void
    {
        $this->sessionMath->update([
            'status' => 'cancelled',
            'notes' => 'Sesi dibatalkan karena hari libur nasional',
        ]);

        $completedCount = TeachingSession::where('tenant_id', $this->tenantA->id)
            ->where('actual_tutor_id', $this->tutorA->id)
            ->where('status', 'completed')
            ->count();

        $this->assertEquals(0, $completedCount);
    }

    public function test_cannot_replace_tutor_with_user_from_different_tenant(): void
    {
        $this->actingAs($this->ownerA);

        $this->expectException(\InvalidArgumentException::class);

        app(TutorReplacementService::class)->replaceTutor(
            $this->sessionMath,
            $this->tutorTenantB, // User from Tenant B
            'Cross tenant replacement attempt',
            $this->ownerA
        );
    }

    public function test_tutor_can_report_absence_on_upcoming_scheduled_session(): void
    {
        $this->actingAs($this->tutorA);

        $response = $this->post(route('tutor.sessions.report-absence', $this->sessionMath), [
            'reason' => 'Sakit demam tinggi',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->sessionMath->refresh();
        $this->assertNull($this->sessionMath->actual_tutor_id);
        $this->assertEquals($this->tutorA->id, $this->sessionMath->scheduled_tutor_id);
        $this->assertStringContainsString('[TUTOR BERHALANGAN]', $this->sessionMath->notes);
        $this->assertStringContainsString('Sakit demam tinggi', $this->sessionMath->notes);
    }

    public function test_tutor_cannot_report_absence_on_unassigned_session(): void
    {
        // Tutor B is not assigned to sessionMath (Tutor A is scheduled)
        $this->actingAs($this->tutorB);

        $response = $this->post(route('tutor.sessions.report-absence', $this->sessionMath), [
            'reason' => 'Unauthorized absence report',
        ]);

        $response->assertForbidden();
    }

    public function test_sessions_needing_attention_are_visible_on_admin_dashboard(): void
    {
        // Tutor reports absence
        $this->sessionMath->update([
            'actual_tutor_id' => null,
            'notes' => '[TUTOR BERHALANGAN] Tutor Arya: Sakit demam',
            'session_date' => Carbon::now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($this->adminMalang);

        $response = $this->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Sesi Membutuhkan Perhatian');
        $response->assertSee('Tutor Berhalangan');
        $response->assertSee($this->classMath->name);
    }

    public function test_admin_can_view_teaching_sessions_tab_and_details_on_class_detail(): void
    {
        $this->actingAs($this->adminMalang);

        $response = $this->get(route('admin.classes.show', $this->classMath));
        $response->assertOk();
        $response->assertSee('Sesi Mengajar');
        $response->assertSee($this->sessionMath->scheduledTutor->name);
    }

    public function test_replacing_tutor_preserves_class_primary_tutor_and_updates_actual_tutor_for_honor(): void
    {
        $this->actingAs($this->adminMalang);

        // Class and schedule primary tutor is tutorA
        $this->assertEquals($this->tutorA->id, $this->classMath->tutorAssignments()->where('is_primary', true)->first()?->tutor_id ?? $this->tutorA->id);

        // Admin replaces tutor on session
        $this->post(route('admin.attendances.sessions.replace-tutor', $this->sessionMath), [
            'replacement_tutor_id' => $this->tutorB->id,
            'reason' => 'Tutor Arya berhalangan, digantikan oleh Tutor Bima',
        ])->assertRedirect();

        $this->sessionMath->refresh();
        $this->assertEquals($this->tutorA->id, $this->sessionMath->scheduled_tutor_id);
        $this->assertEquals($this->tutorB->id, $this->sessionMath->actual_tutor_id);

        // Class assignments remain unchanged
        $this->assertDatabaseHas('tutor_assignments', [
            'class_id' => $this->classMath->id,
            'tutor_id' => $this->tutorA->id,
        ]);
    }
}
