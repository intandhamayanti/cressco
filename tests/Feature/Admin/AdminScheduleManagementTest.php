<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Branch $branchMalang;

    protected Branch $branchMakassar;

    protected User $adminMalang;

    protected User $tutor;

    protected Classes $classMalang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Tutoring Center',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->branchMalang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Prime Academy - Malang',
            'code' => 'MLG-01',
            'status' => 'active',
        ]);

        $this->branchMakassar = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Prime Academy - Makassar',
            'code' => 'MKS-01',
            'status' => 'active',
        ]);

        $this->adminMalang = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Malang',
            'email' => 'admin.malang@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'user_id' => $this->adminMalang->id,
        ]);

        $this->tutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor Budi',
            'email' => 'budi.tutor@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->classMalang = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Fisika Intensif Malang',
            'subject' => 'Fisika',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_create_recurring_schedule(): void
    {
        $payload = [
            'scheduled_tutor_id' => $this->tutor->id,
            'day_of_week' => 1, // Senin
            'start_time' => '14:00',
            'end_time' => '15:30',
            'room' => 'Ruang Teori 1',
            'starts_on' => now()->toDateString(),
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->post("/admin/classes/{$this->classMalang->id}/schedules", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('schedules', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMalang->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'day_of_week' => 1,
            'start_time' => '14:00',
            'end_time' => '15:30',
            'room' => 'Ruang Teori 1',
            'status' => 'active',
        ]);
    }

    public function test_schedule_creation_validates_required_and_time_rules(): void
    {
        // Missing required fields
        $response = $this->actingAs($this->adminMalang)->post("/admin/classes/{$this->classMalang->id}/schedules", []);
        $response->assertSessionHasErrors(['scheduled_tutor_id', 'day_of_week', 'start_time', 'end_time', 'starts_on']);

        // Invalid time range (end_time before start_time)
        $invalidTimePayload = [
            'scheduled_tutor_id' => $this->tutor->id,
            'day_of_week' => 1,
            'start_time' => '16:00',
            'end_time' => '14:00',
            'starts_on' => now()->toDateString(),
        ];

        $timeResponse = $this->actingAs($this->adminMalang)->post("/admin/classes/{$this->classMalang->id}/schedules", $invalidTimePayload);
        $timeResponse->assertSessionHasErrors('end_time');
    }

    public function test_admin_can_update_recurring_schedule(): void
    {
        $schedule = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMalang->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'day_of_week' => 2,
            'start_time' => '10:00',
            'end_time' => '11:30',
            'room' => 'Ruang A',
            'starts_on' => now()->toDateString(),
            'status' => 'active',
        ]);

        $payload = [
            'scheduled_tutor_id' => $this->tutor->id,
            'day_of_week' => 3, // Rabu
            'start_time' => '13:00',
            'end_time' => '14:30',
            'room' => 'Lab Fisika',
            'starts_on' => now()->toDateString(),
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->put("/admin/classes/{$this->classMalang->id}/schedules/{$schedule->id}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('schedules', [
            'id' => $schedule->id,
            'day_of_week' => 3,
            'start_time' => '13:00',
            'end_time' => '14:30',
            'room' => 'Lab Fisika',
        ]);
    }

    public function test_admin_can_toggle_schedule_status(): void
    {
        $schedule = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMalang->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'day_of_week' => 1,
            'start_time' => '09:00',
            'end_time' => '10:30',
            'starts_on' => now()->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->patch("/admin/classes/{$this->classMalang->id}/schedules/{$schedule->id}/toggle-status");
        $response->assertRedirect();
        $this->assertEquals('inactive', $schedule->fresh()->status);

        $this->actingAs($this->adminMalang)->patch("/admin/classes/{$this->classMalang->id}/schedules/{$schedule->id}/toggle-status");
        $this->assertEquals('active', $schedule->fresh()->status);
    }

    public function test_admin_can_delete_schedule(): void
    {
        $schedule = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMalang->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'day_of_week' => 5,
            'start_time' => '16:00',
            'end_time' => '17:30',
            'starts_on' => now()->toDateString(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminMalang)->delete("/admin/classes/{$this->classMalang->id}/schedules/{$schedule->id}");
        $response->assertRedirect();
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_admin_cannot_manage_schedule_on_class_in_inaccessible_branch(): void
    {
        $classMakassar = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Kelas Makassar',
            'status' => 'active',
        ]);

        $payload = [
            'scheduled_tutor_id' => $this->tutor->id,
            'day_of_week' => 1,
            'start_time' => '10:00',
            'end_time' => '11:30',
            'starts_on' => now()->toDateString(),
            'status' => 'active',
        ];

        $response = $this->actingAs($this->adminMalang)->post("/admin/classes/{$classMakassar->id}/schedules", $payload);
        $response->assertForbidden();
    }
}
