<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $owner;

    private User $tutor;

    private Branch $branch;

    private Classes $class;

    private TeachingSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Malang',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->owner = User::factory()->owner()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->tutor = User::factory()->tutor()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Rian Hidayat, M.Kom.',
        ]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Malang',
            'code' => 'MLG',
            'status' => 'active',
        ]);

        $this->class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Programming Dasar A',
            'subject' => 'Programming',
            'level' => 'Pemula',
            'capacity' => 15,
            'status' => 'active',
        ]);

        $schedule = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'class_id' => $this->class->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'day_of_week' => 1,
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Lab 1',
            'starts_on' => '2026-01-05',
            'status' => 'active',
        ]);

        $this->session = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'schedule_id' => $schedule->id,
            'class_id' => $this->class->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'actual_tutor_id' => $this->tutor->id,
            'session_date' => '2026-09-14',
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Lab 1',
            'status' => 'completed',
            'material' => 'Pengenalan Python',
        ]);
    }

    public function test_owner_can_view_attendance_oversight_index(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('owner.attendances.index'));

        $response->assertOk();
        $response->assertSee('Monitoring Absensi');
        $response->assertSee('Cabang Malang');
        $response->assertSee('Programming Dasar A');
    }

    public function test_owner_can_view_teaching_session_attendance_details(): void
    {
        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Andi Pratama',
            'status' => 'active',
        ]);

        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'teaching_session_id' => $this->session->id,
            'student_id' => $student->id,
            'status' => 'hadir',
            'recorded_at' => now(),
            'recorded_by' => $this->tutor->id,
        ]);

        TutorAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'teaching_session_id' => $this->session->id,
            'tutor_id' => $this->tutor->id,
            'status' => 'present',
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.attendances.sessions.show', $this->session));

        $response->assertOk();
        $response->assertSee('Detail Sesi: Programming Dasar A');
        $response->assertSee('Daftar Presensi Siswa');
        $response->assertSee('Andi Pratama');
        $response->assertSee('Rian Hidayat, M.Kom.');
        $response->assertSee('Pengenalan Python');
    }
}
