<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerClassManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Branch $branchA1;

    protected Branch $branchA2;

    protected Branch $branchB;

    protected User $ownerA;

    protected User $adminA;

    protected User $tutorA;

    protected User $tutorB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Surabaya',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Competitor Bimbel',
            'slug' => 'competitor-bimbel',
            'status' => 'active',
        ]);

        $this->branchA1 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Gubeng',
            'code' => 'GBG',
            'city' => 'Surabaya',
            'status' => 'active',
        ]);

        $this->branchA2 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Rungkut',
            'code' => 'RKT',
            'city' => 'Surabaya',
            'status' => 'active',
        ]);

        $this->branchB = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Kuningan',
            'code' => 'KNG',
            'city' => 'Jakarta',
            'status' => 'active',
        ]);

        $this->ownerA = User::factory()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->adminA = User::factory()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->tutorA = User::factory()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Kak Farhan Master Fisika',
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->tutorB = User::factory()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'role' => 'tutor',
            'status' => 'active',
        ]);
    }

    public function test_owner_can_list_classes(): void
    {
        $class1 = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => '12 IPA - Fisika UTBK',
            'subject' => 'Fisika',
            'level' => '12 SMA',
            'capacity' => 15,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->ownerA)->get('/owner/classes');

        $response->assertOk();
        $response->assertSee('Management Kelas & Jadwal');
        $response->assertSee('12 IPA - Fisika UTBK');
        $response->assertSee('Gubeng');
    }

    public function test_owner_cannot_see_other_tenant_classes(): void
    {
        $classA = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Kelas Tenant A',
            'subject' => 'Biologi',
            'status' => 'active',
        ]);

        $classB = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB->id,
            'name' => 'Kelas Rahasia Tenant B',
            'subject' => 'Kimia',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->ownerA)->get('/owner/classes');

        $response->assertOk();
        $response->assertSee('Kelas Tenant A');
        $response->assertDontSee('Kelas Rahasia Tenant B');
    }

    public function test_owner_can_create_class_with_optional_tutor(): void
    {
        $payload = [
            'branch_id' => $this->branchA1->id,
            'name' => '10 SMA - Matematika Peminatan',
            'subject' => 'Matematika',
            'level' => '10 SMA',
            'capacity' => 20,
            'status' => 'active',
            'tutor_id' => $this->tutorA->id,
        ];

        $response = $this->actingAs($this->ownerA)->post('/owner/classes', $payload);

        $class = Classes::where('name', '10 SMA - Matematika Peminatan')->first();
        $this->assertNotNull($class);
        $this->assertEquals($this->tenantA->id, $class->tenant_id);
        $this->assertEquals($this->branchA1->id, $class->branch_id);

        $response->assertRedirect(route('owner.classes.show', $class));

        // Verify initial tutor assignment was created
        $this->assertDatabaseHas('tutor_assignments', [
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'class_id' => $class->id,
            'tutor_id' => $this->tutorA->id,
            'status' => 'active',
        ]);
    }

    public function test_class_creation_rejects_non_tenant_branch(): void
    {
        $payload = [
            'branch_id' => $this->branchB->id, // Belongs to Tenant B
            'name' => 'Kelas Invalid Branch',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->ownerA)->post('/owner/classes', $payload);

        $response->assertSessionHasErrors('branch_id');
        $this->assertDatabaseMissing('classes', [
            'name' => 'Kelas Invalid Branch',
        ]);
    }

    public function test_owner_can_view_class_details_including_schedules_tutors_and_students(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => '11 IPA - Kimia Organik',
            'subject' => 'Kimia',
            'level' => '11 SMA',
            'capacity' => 12,
            'status' => 'active',
        ]);

        // Assign tutor
        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'tutor_id' => $this->tutorA->id,
            'class_id' => $class->id,
            'status' => 'active',
        ]);

        // Add recurring schedule
        Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'class_id' => $class->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 1, // Senin
            'start_time' => '18:30:00',
            'end_time' => '20:00:00',
            'room' => 'Lab Kimia 1',
            'starts_on' => now()->toDateString(),
            'status' => 'active',
        ]);

        // Add student enrollment
        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Bintang Kejora',
            'gender' => 'Laki-laki',
            'status' => 'active',
        ]);

        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'student_id' => $student->id,
            'class_id' => $class->id,
            'started_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->ownerA)->get("/owner/classes/{$class->id}");

        $response->assertOk();
        $response->assertSee('11 IPA - Kimia Organik');
        $response->assertSee('Kak Farhan Master Fisika');
        $response->assertSee('Setiap Senin');
        $response->assertSee('18:30 - 20:00 WIB');
        $response->assertSee('Lab Kimia 1');
        $response->assertSee('Bintang Kejora');
    }

    public function test_owner_cannot_view_other_tenant_class_details(): void
    {
        $classB = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB->id,
            'name' => 'Kelas Tenant B',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->ownerA)->get("/owner/classes/{$classB->id}");

        $response->assertNotFound();
    }

    public function test_owner_can_update_class(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Nama Lama',
            'subject' => 'Fisika',
            'level' => '10 SMA',
            'capacity' => 10,
            'status' => 'active',
        ]);

        $payload = [
            'branch_id' => $this->branchA2->id,
            'name' => 'Nama Baru Diperbarui',
            'subject' => 'Fisika Terapan',
            'level' => '11 SMA',
            'capacity' => 25,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->ownerA)->put("/owner/classes/{$class->id}", $payload);

        $response->assertRedirect();
        $class->refresh();
        $this->assertEquals('Nama Baru Diperbarui', $class->name);
        $this->assertEquals($this->branchA2->id, $class->branch_id);
        $this->assertEquals('Fisika Terapan', $class->subject);
        $this->assertEquals(25, $class->capacity);
    }

    public function test_owner_can_toggle_class_status(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Kelas Aktif',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->ownerA)->patch("/owner/classes/{$class->id}/toggle-status");

        $response->assertRedirect();
        $class->refresh();
        $this->assertEquals('inactive', $class->status);

        // Toggle back to active
        $this->actingAs($this->ownerA)->patch("/owner/classes/{$class->id}/toggle-status");
        $class->refresh();
        $this->assertEquals('active', $class->status);
    }

    public function test_owner_can_assign_tutor_and_toggle_assignment(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Kelas Siap Tutor',
            'status' => 'active',
        ]);

        $payload = [
            'tutor_id' => $this->tutorA->id,
            'started_at' => '2026-02-01',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->ownerA)->post("/owner/classes/{$class->id}/tutors", $payload);

        $response->assertRedirect();
        $assignment = TutorAssignment::where('class_id', $class->id)->where('tutor_id', $this->tutorA->id)->first();
        $this->assertNotNull($assignment);
        $this->assertEquals('active', $assignment->status);

        // Toggle tutor assignment status
        $toggleRes = $this->actingAs($this->ownerA)->patch("/owner/classes/{$class->id}/tutors/{$assignment->id}/toggle-status");
        $toggleRes->assertRedirect();
        $assignment->refresh();
        $this->assertEquals('inactive', $assignment->status);
    }

    public function test_owner_can_create_recurring_schedule(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Kelas Reguler Senin Sore',
            'status' => 'active',
        ]);

        $payload = [
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 1, // Senin
            'start_time' => '16:00',
            'end_time' => '17:30',
            'room' => 'Ruang Galileo 2',
            'starts_on' => '2026-01-10',
            'ends_on' => '2026-06-30',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->ownerA)->post("/owner/classes/{$class->id}/schedules", $payload);

        $response->assertRedirect();

        $schedule = Schedule::where('class_id', $class->id)->first();
        $this->assertNotNull($schedule);
        $this->assertEquals(1, $schedule->day_of_week);
        $this->assertEquals('Ruang Galileo 2', $schedule->room);
        $this->assertEquals($this->tutorA->id, $schedule->scheduled_tutor_id);
    }

    public function test_schedule_creation_rejects_non_tenant_tutor_or_invalid_time(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Kelas Error Schedule',
            'status' => 'active',
        ]);

        // Non-tenant tutor
        $payloadForeignTutor = [
            'scheduled_tutor_id' => $this->tutorB->id, // Tenant B
            'day_of_week' => 2,
            'start_time' => '16:00',
            'end_time' => '17:30',
            'starts_on' => '2026-01-10',
        ];

        $resForeign = $this->actingAs($this->ownerA)->post("/owner/classes/{$class->id}/schedules", $payloadForeignTutor);
        $resForeign->assertSessionHasErrors('scheduled_tutor_id');

        // End time before start time
        $payloadInvalidTime = [
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 2,
            'start_time' => '17:30',
            'end_time' => '16:00', // Invalid: before start_time
            'starts_on' => '2026-01-10',
        ];

        $resTime = $this->actingAs($this->ownerA)->post("/owner/classes/{$class->id}/schedules", $payloadInvalidTime);
        $resTime->assertSessionHasErrors('end_time');
    }

    public function test_owner_can_update_and_delete_recurring_schedule(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Kelas Siap Edit Schedule',
            'status' => 'active',
        ]);

        $schedule = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'class_id' => $class->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 3, // Rabu
            'start_time' => '14:00:00',
            'end_time' => '15:30:00',
            'room' => 'Ruang Lama',
            'starts_on' => '2026-01-01',
            'status' => 'active',
        ]);

        $updatePayload = [
            'scheduled_tutor_id' => $this->tutorA->id,
            'day_of_week' => 4, // Kamis
            'start_time' => '15:00',
            'end_time' => '16:30',
            'room' => 'Ruang Baru M-2',
            'starts_on' => '2026-01-01',
            'status' => 'active',
        ];

        $updateRes = $this->actingAs($this->ownerA)->put("/owner/classes/{$class->id}/schedules/{$schedule->id}", $updatePayload);
        $updateRes->assertRedirect();
        $schedule->refresh();
        $this->assertEquals(4, $schedule->day_of_week);
        $this->assertEquals('Ruang Baru M-2', $schedule->room);

        // Delete schedule
        $deleteRes = $this->actingAs($this->ownerA)->delete("/owner/classes/{$class->id}/schedules/{$schedule->id}");
        $deleteRes->assertRedirect();
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_class_search_and_branch_filtering(): void
    {
        Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Matematika Diskrit Gubeng',
            'subject' => 'Matematika',
            'status' => 'active',
        ]);

        Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'name' => 'Biologi Molekuler Rungkut',
            'subject' => 'Biologi',
            'status' => 'inactive',
        ]);

        // Search query
        $resSearch = $this->actingAs($this->ownerA)->get('/owner/classes?search=Diskrit');
        $resSearch->assertOk();
        $resSearch->assertSee('Matematika Diskrit Gubeng');
        $resSearch->assertDontSee('Biologi Molekuler Rungkut');

        // Branch filter
        $resBranch = $this->actingAs($this->ownerA)->get("/owner/classes?branch_id={$this->branchA2->id}");
        $resBranch->assertOk();
        $resBranch->assertSee('Biologi Molekuler Rungkut');
        $resBranch->assertDontSee('Matematika Diskrit Gubeng');

        // Status filter
        $resStatus = $this->actingAs($this->ownerA)->get('/owner/classes?status=inactive');
        $resStatus->assertOk();
        $resStatus->assertSee('Biologi Molekuler Rungkut');
        $resStatus->assertDontSee('Matematika Diskrit Gubeng');
    }
}
