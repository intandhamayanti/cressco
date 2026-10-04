<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Schedule;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TeachingSessionGenerationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RecurringScheduleSessionGenerationTest extends TestCase
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

    protected Classes $classMath;

    protected Classes $classPhysicsInactive;

    protected Schedule $scheduleMathMonday;

    protected Schedule $scheduleMathWednesday;

    protected TeachingSessionGenerationService $generationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generationService = app(TeachingSessionGenerationService::class);

        // Tenant A
        $this->tenantA = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Tutoring',
            'slug' => 'prime-academy',
            'domain' => 'prime.cressco.test',
            'status' => 'active',
        ]);

        // Tenant B
        $this->tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Nexus Bimbel Nusantara',
            'slug' => 'nexus-bimbel',
            'domain' => 'nexus.cressco.test',
            'status' => 'active',
        ]);

        // Branches Tenant A
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

        // Branch Tenant B
        $this->branchTenantB = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Cabang Bandung B',
            'code' => 'BDG-01',
            'status' => 'active',
        ]);

        // Users Tenant A
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
            'name' => 'Budi Tutor Matematika',
            'email' => 'budi.tutor@prime.test',
            'password' => Hash::make('password123'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->tutorB = User::create([
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
            'name' => '12 SMA Matematika Intensif',
            'subject' => 'Matematika',
            'status' => 'active',
        ]);

        $this->classPhysicsInactive = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'name' => '11 SMA Fisika Nonaktif',
            'subject' => 'Fisika',
            'status' => 'inactive',
        ]);

        // Schedules
        // day_of_week: 1 = Monday
        $this->scheduleMathMonday = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 1, // Monday
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'room' => 'Ruang Teori 1',
            'starts_on' => '2026-10-01',
            'ends_on' => '2026-10-31',
            'status' => 'active',
        ]);

        // day_of_week: 3 = Wednesday
        $this->scheduleMathWednesday = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 3, // Wednesday
            'start_time' => '16:30:00',
            'end_time' => '18:00:00',
            'room' => 'Ruang Teori 2',
            'starts_on' => '2026-10-01',
            'ends_on' => null,
            'status' => 'active',
        ]);
    }

    public function test_can_generate_teaching_sessions_from_single_recurring_schedule(): void
    {
        // October 2026:
        // Mondays: Oct 5, Oct 12, Oct 19, Oct 26 (4 Mondays)
        $result = $this->generationService->generateForSchedule(
            $this->scheduleMathMonday,
            '2026-10-01',
            '2026-10-31'
        );

        $this->assertEquals(4, $result['generated_count']);
        $this->assertEquals(0, $result['skipped_existing_count']);
        $this->assertCount(4, $result['sessions']);

        $this->assertDatabaseCount('teaching_sessions', 4);

        $firstSession = TeachingSession::where('schedule_id', $this->scheduleMathMonday->id)
            ->whereDate('session_date', '2026-10-05')
            ->first();

        $this->assertNotNull($firstSession);
        $this->assertEquals($this->tenantA->id, $firstSession->tenant_id);
        $this->assertEquals($this->branchMalang->id, $firstSession->branch_id);
        $this->assertEquals($this->classMath->id, $firstSession->class_id);
        $this->assertEquals($this->tutorA->id, $firstSession->scheduled_tutor_id);
        $this->assertEquals($this->tutorA->id, $firstSession->actual_tutor_id);
        $this->assertEquals('14:00:00', $firstSession->start_time);
        $this->assertEquals('16:00:00', $firstSession->end_time);
        $this->assertEquals('Ruang Teori 1', $firstSession->room);
        $this->assertEquals('scheduled', $firstSession->status);
    }

    public function test_repeated_generation_does_not_create_duplicates(): void
    {
        // First run: generates 4 sessions
        $firstResult = $this->generationService->generateForSchedule(
            $this->scheduleMathMonday,
            '2026-10-01',
            '2026-10-31'
        );
        $this->assertEquals(4, $firstResult['generated_count']);
        $this->assertDatabaseCount('teaching_sessions', 4);

        // Second run with same date range: generates 0, skips 4
        $secondResult = $this->generationService->generateForSchedule(
            $this->scheduleMathMonday,
            '2026-10-01',
            '2026-10-31'
        );
        $this->assertEquals(0, $secondResult['generated_count']);
        $this->assertEquals(4, $secondResult['skipped_existing_count']);
        $this->assertDatabaseCount('teaching_sessions', 4);
    }

    public function test_respects_schedule_starts_on_and_ends_on_dates(): void
    {
        // Schedule starts on 2026-10-01 and ends on 2026-10-31
        // Range requested: 2026-09-01 to 2026-11-30
        // September Mondays (outside starts_on) and November Mondays (outside ends_on) must not generate sessions.
        $result = $this->generationService->generateForSchedule(
            $this->scheduleMathMonday,
            '2026-09-01',
            '2026-11-30'
        );

        $this->assertEquals(4, $result['generated_count']);

        // Check all generated sessions are in October 2026
        $sessions = TeachingSession::where('schedule_id', $this->scheduleMathMonday->id)->get();
        foreach ($sessions as $session) {
            $this->assertTrue(Carbon::parse($session->session_date)->between('2026-10-01', '2026-10-31'));
        }
    }

    public function test_inactive_schedule_or_inactive_class_does_not_generate_sessions(): void
    {
        $inactiveSchedule = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 2, // Tuesday
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'starts_on' => '2026-10-01',
            'status' => 'inactive',
        ]);

        $scheduleForInactiveClass = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classPhysicsInactive->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 4, // Thursday
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'starts_on' => '2026-10-01',
            'status' => 'active',
        ]);

        $resultInactiveSchedule = $this->generationService->generateForSchedule(
            $inactiveSchedule,
            '2026-10-01',
            '2026-10-31'
        );
        $this->assertEquals(0, $resultInactiveSchedule['generated_count']);

        $resultForClass = $this->generationService->generateForClass(
            $this->classPhysicsInactive,
            '2026-10-01',
            '2026-10-31'
        );
        $this->assertEquals(0, $resultForClass['generated_count']);
    }

    public function test_can_generate_for_entire_tenant_with_multiple_classes_and_schedules(): void
    {
        // October 2026 has:
        // 4 Mondays (Oct 5, 12, 19, 26) for scheduleMathMonday
        // 4 Wednesdays (Oct 7, 14, 21, 28) for scheduleMathWednesday
        // Total = 8 sessions
        $result = $this->generationService->generateForTenant(
            $this->tenantA,
            '2026-10-01',
            '2026-10-31'
        );

        $this->assertEquals(8, $result['generated_count']);
        $this->assertEquals(0, $result['skipped_existing_count']);
        $this->assertDatabaseCount('teaching_sessions', 8);
    }

    public function test_tenant_isolation_during_session_generation(): void
    {
        // Create schedule in Tenant B
        $classB = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchTenantB->id,
            'name' => '10 SMA Biologi Tenant B',
            'subject' => 'Biologi',
            'status' => 'active',
        ]);

        Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchTenantB->id,
            'class_id' => $classB->id,
            'scheduled_tutor_id' => $this->tutorB->id,
            'day_of_week' => 1, // Monday
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'starts_on' => '2026-10-01',
            'status' => 'active',
        ]);

        // Generate for Tenant A only
        $resultA = $this->generationService->generateForTenant(
            $this->tenantA,
            '2026-10-01',
            '2026-10-31'
        );

        // Should only generate 8 sessions for Tenant A
        $this->assertEquals(8, $resultA['generated_count']);

        // Verify no session created for Tenant B
        $this->assertDatabaseCount('teaching_sessions', 8);
        $this->assertEquals(0, TeachingSession::where('tenant_id', $this->tenantB->id)->count());

        // Generate for Tenant B
        $resultB = $this->generationService->generateForTenant(
            $this->tenantB,
            '2026-10-01',
            '2026-10-31'
        );

        $this->assertEquals(4, $resultB['generated_count']);
        $this->assertEquals(4, TeachingSession::where('tenant_id', $this->tenantB->id)->count());
        $this->assertEquals(12, TeachingSession::count());
    }

    public function test_artisan_command_generates_sessions_correctly(): void
    {
        $this->artisan('sessions:generate', [
            '--tenant' => $this->tenantA->slug,
            '--from' => '2026-10-01',
            '--to' => '2026-10-31',
        ])
            ->assertSuccessful();

        $this->assertDatabaseCount('teaching_sessions', 8);

        // Running it again will skip existing
        $this->artisan('sessions:generate', [
            '--tenant' => $this->tenantA->slug,
            '--from' => '2026-10-01',
            '--to' => '2026-10-31',
        ])
            ->assertSuccessful();

        $this->assertDatabaseCount('teaching_sessions', 8);
    }

    public function test_owner_creating_schedule_automatically_generates_initial_teaching_sessions(): void
    {
        $this->actingAs($this->ownerA);

        $scheduleData = [
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 5, // Friday
            'start_time' => '13:30',
            'end_time' => '15:00',
            'room' => 'Ruang 301',
            'starts_on' => '2026-10-01',
            'ends_on' => '2026-10-31',
            'status' => 'active',
        ];

        $response = $this->post(route('owner.classes.schedules.store', $this->classMath->id), $scheduleData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // October 2026 Fridays: Oct 2, Oct 9, Oct 16, Oct 23, Oct 30 (5 Fridays)
        $newSchedule = Schedule::where('day_of_week', 5)->where('class_id', $this->classMath->id)->first();
        $this->assertNotNull($newSchedule);

        $sessionsCount = TeachingSession::where('schedule_id', $newSchedule->id)->count();
        $this->assertEquals(5, $sessionsCount);
    }

    public function test_admin_creating_schedule_in_accessible_branch_generates_sessions(): void
    {
        $this->actingAs($this->adminMalang);

        $scheduleData = [
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 2, // Tuesday
            'start_time' => '15:00',
            'end_time' => '16:30',
            'room' => 'Ruang Lab 1',
            'starts_on' => '2026-10-01',
            'ends_on' => '2026-10-31',
            'status' => 'active',
        ];

        $response = $this->post(route('admin.classes.schedules.store', $this->classMath->id), $scheduleData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // October 2026 Tuesdays: Oct 6, 13, 20, 27 (4 Tuesdays)
        $newSchedule = Schedule::where('day_of_week', 2)->where('class_id', $this->classMath->id)->first();
        $this->assertNotNull($newSchedule);

        $sessionsCount = TeachingSession::where('schedule_id', $newSchedule->id)->count();
        $this->assertEquals(4, $sessionsCount);
    }

    public function test_admin_cannot_create_schedule_for_inaccessible_branch(): void
    {
        $this->actingAs($this->adminMalang);

        $classSurabaya = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchSurabaya->id,
            'name' => '10 SMA Kimia Surabaya',
            'status' => 'active',
        ]);

        $scheduleData = [
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 2,
            'start_time' => '15:00',
            'end_time' => '16:30',
            'starts_on' => '2026-10-01',
            'status' => 'active',
        ];

        $response = $this->post(route('admin.classes.schedules.store', $classSurabaya->id), $scheduleData);

        $response->assertForbidden();
    }
}
