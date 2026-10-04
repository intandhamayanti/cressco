<?php

namespace Tests\Feature\Admin;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAttendanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Tenant $otherTenant;

    protected Branch $branchMalang;

    protected Branch $branchMakassar;

    protected Branch $branchOtherTenant;

    protected User $adminMalang;

    protected User $tutor;

    protected Classes $classMalang;

    protected TeachingSession $sessionMalang;

    protected Student $studentMalang;

    protected StudentAttendance $attendanceMalang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Tutoring Center',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Other Tutoring Center',
            'slug' => 'other-academy',
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

        $this->branchOtherTenant = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Other Branch',
            'code' => 'OTH-01',
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
            'name' => 'Biologi Malang Kelas A',
            'subject' => 'Biologi',
            'status' => 'active',
        ]);

        $this->sessionMalang = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMalang->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'actual_tutor_id' => $this->tutor->id,
            'session_date' => now()->toDateString(),
            'start_time' => '14:00',
            'end_time' => '15:30',
            'status' => 'completed',
        ]);

        $this->studentMalang = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => 'Ahmad Santoso',
            'email' => 'ahmad@primeacademy.test',
            'nis' => 'NIS-1001',
            'status' => 'active',
        ]);

        $this->attendanceMalang = StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'student_id' => $this->studentMalang->id,
            'teaching_session_id' => $this->sessionMalang->id,
            'status' => 'hadir',
            'recorded_at' => now(),
            'recorded_by' => $this->tutor->id,
        ]);
    }

    public function test_guest_cannot_access_attendances(): void
    {
        $response = $this->get('/admin/attendances');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_attendances_scoped_to_accessible_branch(): void
    {
        // Another attendance in Makassar (inaccessible to Admin Malang)
        $classMakassar = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Fisika Makassar Kelas B',
            'status' => 'active',
        ]);

        $sessionMakassar = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'class_id' => $classMakassar->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'session_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'completed',
        ]);

        $studentMakassar = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Dewi Makassar',
            'email' => 'dewi@makassar.test',
            'nis' => 'NIS-2002',
            'status' => 'active',
        ]);

        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'student_id' => $studentMakassar->id,
            'teaching_session_id' => $sessionMakassar->id,
            'status' => 'hadir',
            'recorded_at' => now(),
            'recorded_by' => $this->tutor->id,
        ]);

        $response = $this->actingAs($this->adminMalang)->get('/admin/attendances');

        $response->assertOk();
        $response->assertSee('Ahmad Santoso');
        $response->assertDontSee('Dewi Makassar');
    }

    public function test_admin_can_filter_attendances_by_search_class_and_status(): void
    {
        $response = $this->actingAs($this->adminMalang)->get('/admin/attendances?search=Ahmad');
        $response->assertOk();
        $response->assertSee('Ahmad Santoso');

        $responseClass = $this->actingAs($this->adminMalang)->get("/admin/attendances?class_id={$this->classMalang->id}");
        $responseClass->assertOk();
        $responseClass->assertSee('Ahmad Santoso');

        $responseStatus = $this->actingAs($this->adminMalang)->get('/admin/attendances?status=sakit');
        $responseStatus->assertOk();
        $responseStatus->assertDontSee('Ahmad Santoso'); // since status is hadir
    }

    public function test_admin_can_view_session_attendances(): void
    {
        $response = $this->actingAs($this->adminMalang)->get("/admin/attendances/sessions/{$this->sessionMalang->id}");

        $response->assertOk();
        $response->assertSee('Presensi Sesi: Biologi Malang Kelas A');
        $response->assertSee('Ahmad Santoso');
    }

    public function test_admin_cannot_view_session_attendances_in_inaccessible_branch(): void
    {
        $classMakassar = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Fisika Makassar Rahasia',
            'status' => 'active',
        ]);

        $sessionMakassar = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'class_id' => $classMakassar->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'session_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->adminMalang)->get("/admin/attendances/sessions/{$sessionMakassar->id}");
        $response->assertNotFound();
    }

    public function test_admin_can_correct_attendance(): void
    {
        $payload = [
            'status' => 'izin',
            'note' => 'Izin keperluan keluarga mendadak, konfirmasi ortu',
        ];

        $response = $this->actingAs($this->adminMalang)->put("/admin/attendances/{$this->attendanceMalang->id}", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('student_attendances', [
            'id' => $this->attendanceMalang->id,
            'status' => 'izin',
            'note' => 'Izin keperluan keluarga mendadak, konfirmasi ortu',
        ]);
    }

    public function test_admin_cannot_correct_attendance_in_inaccessible_branch(): void
    {
        $classMakassar = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Biologi Makassar',
            'status' => 'active',
        ]);

        $sessionMakassar = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'class_id' => $classMakassar->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'session_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'completed',
        ]);

        $studentMakassar = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'name' => 'Dewi Makassar',
            'status' => 'active',
        ]);

        $attMakassar = StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMakassar->id,
            'student_id' => $studentMakassar->id,
            'teaching_session_id' => $sessionMakassar->id,
            'status' => 'hadir',
            'recorded_at' => now(),
            'recorded_by' => $this->tutor->id,
        ]);

        $payload = [
            'status' => 'alpa',
            'note' => 'Koreksi ilegal',
        ];

        $response = $this->actingAs($this->adminMalang)->put("/admin/attendances/{$attMakassar->id}", $payload);
        $response->assertForbidden();
    }

    public function test_cross_tenant_isolation_for_attendances(): void
    {
        $tutorOther = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Tutor Other',
            'email' => 'tutor.other@other.test',
            'password' => bcrypt('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $classOther = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'name' => 'Kelas Other Tenant',
            'status' => 'active',
        ]);

        $sessionOther = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'class_id' => $classOther->id,
            'scheduled_tutor_id' => $tutorOther->id,
            'session_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'completed',
        ]);

        $studentOther = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'name' => 'Siswa Other',
            'status' => 'active',
        ]);

        $attOther = StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->otherTenant->id,
            'branch_id' => $this->branchOtherTenant->id,
            'student_id' => $studentOther->id,
            'teaching_session_id' => $sessionOther->id,
            'status' => 'hadir',
            'recorded_at' => now(),
            'recorded_by' => $tutorOther->id,
        ]);

        $response = $this->actingAs($this->adminMalang)->put("/admin/attendances/{$attOther->id}", [
            'status' => 'izin',
        ]);
        $response->assertForbidden();
    }
}
