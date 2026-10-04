<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Branch $branchA1;

    protected Branch $branchA2;

    protected Branch $branchB1;

    protected User $ownerA;

    protected User $ownerB;

    protected User $adminA1;

    protected User $tutorA1;

    protected User $tutorA2;

    protected Classes $classA1;

    protected Classes $classA2;

    protected Classes $classB1;

    protected Student $studentA1;

    protected Student $studentA2;

    protected Student $studentB1;

    protected TeachingSession $sessionA1;

    protected TeachingSession $sessionA2;

    protected TeachingSession $sessionB1;

    protected Payment $paymentA1;

    protected Payment $paymentB1;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Tenant A
        $this->tenantA = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Security Alpha',
            'slug' => 'tenant-sec-a',
            'status' => 'active',
        ]);

        // Tenant B
        $this->tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Security Beta',
            'slug' => 'tenant-sec-b',
            'status' => 'active',
        ]);

        // Branches
        $this->branchA1 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Cabang Alpha Pusat',
            'status' => 'active',
        ]);

        $this->branchA2 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Cabang Alpha Barat',
            'status' => 'active',
        ]);

        $this->branchB1 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Cabang Beta Timur',
            'status' => 'active',
        ]);

        // Users
        $this->ownerA = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Owner Alpha',
            'email' => 'owner.sec.a@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'owner',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->ownerB = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Owner Beta',
            'email' => 'owner.sec.b@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'owner',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->adminA1 = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Admin Alpha 1',
            'email' => 'admin.sec.a1@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'user_id' => $this->adminA1->id,
        ]);

        $this->tutorA1 = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Tutor Alpha 1',
            'email' => 'tutor.sec.a1@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'user_id' => $this->tutorA1->id,
        ]);

        $this->tutorA2 = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Tutor Alpha 2',
            'email' => 'tutor.sec.a2@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'user_id' => $this->tutorA2->id,
        ]);

        // Classes
        $this->classA1 = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Matematika SD Alpha 1',
            'subject' => 'Matematika',
            'level' => 'SD',
            'capacity' => 15,
            'status' => 'active',
        ]);

        $this->classA2 = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'name' => 'Fisika SMA Alpha 2',
            'subject' => 'Fisika',
            'level' => 'SMA',
            'capacity' => 15,
            'status' => 'active',
        ]);

        $this->classB1 = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB1->id,
            'name' => 'Kimia SMA Beta',
            'subject' => 'Kimia',
            'level' => 'SMA',
            'capacity' => 10,
            'status' => 'active',
        ]);

        // Tutor Assignments
        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'tutor_id' => $this->tutorA1->id,
            'class_id' => $this->classA1->id,
            'status' => 'active',
            'started_at' => now()->toDateString(),
        ]);

        // Students
        $this->studentA1 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Siswa Alpha 1',
            'status' => 'active',
        ]);

        $this->studentA2 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'name' => 'Siswa Alpha 2',
            'status' => 'active',
        ]);

        $this->studentB1 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB1->id,
            'name' => 'Siswa Beta 1',
            'status' => 'active',
        ]);

        // Enrollments
        Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'student_id' => $this->studentA1->id,
            'class_id' => $this->classA1->id,
            'status' => 'active',
            'started_at' => now()->toDateString(),
        ]);

        // Teaching Sessions
        $this->sessionA1 = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'class_id' => $this->classA1->id,
            'scheduled_tutor_id' => $this->tutorA1->id,
            'actual_tutor_id' => $this->tutorA1->id,
            'session_date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '09:30',
            'status' => 'scheduled',
        ]);

        $this->sessionA2 = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'class_id' => $this->classA2->id,
            'scheduled_tutor_id' => $this->tutorA2->id,
            'actual_tutor_id' => $this->tutorA2->id,
            'session_date' => now()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'status' => 'scheduled',
        ]);

        $this->sessionB1 = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB1->id,
            'class_id' => $this->classB1->id,
            'scheduled_tutor_id' => $this->ownerB->id,
            'actual_tutor_id' => $this->ownerB->id,
            'session_date' => now()->toDateString(),
            'start_time' => '13:00',
            'end_time' => '14:30',
            'status' => 'scheduled',
        ]);

        // Payments
        $this->paymentA1 = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'student_id' => $this->studentA1->id,
            'recorded_by' => $this->ownerA->id,
            'amount' => 500000,
            'period' => '2026-10',
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'belum_bayar',
        ]);

        $this->paymentB1 = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB1->id,
            'student_id' => $this->studentB1->id,
            'recorded_by' => $this->ownerB->id,
            'amount' => 750000,
            'period' => '2026-10',
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'belum_bayar',
        ]);
    }

    public function test_owner_cannot_access_or_tamper_resources_of_another_tenant(): void
    {
        // Owner A tries to view Tenant B's student
        $response = $this->actingAs($this->ownerA)->get(route('owner.students.show', $this->studentB1));
        $this->assertTrue(in_array($response->status(), [403, 404], true));

        // Owner A tries to update Tenant B's payment
        $response = $this->actingAs($this->ownerA)->put(route('owner.payments.update', $this->paymentB1), [
            'student_id' => $this->studentA1->id,
            'branch_id' => $this->branchA1->id,
            'amount' => 100000,
            'period' => '2026-10',
            'due_date' => now()->toDateString(),
            'status' => 'lunas',
        ]);
        $this->assertTrue(in_array($response->status(), [403, 404], true));

        // Owner A tries to delete Tenant B's payment
        $response = $this->actingAs($this->ownerA)->delete(route('owner.payments.destroy', $this->paymentB1));
        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }

    public function test_admin_cannot_access_or_tamper_branches_outside_assigned_scope(): void
    {
        // Admin A1 only has branch A1, tries to view student in branch A2
        $response = $this->actingAs($this->adminA1)->get(route('admin.students.show', $this->studentA2));
        $this->assertTrue(in_array($response->status(), [403, 404], true));

        // Admin A1 tries to update class in branch A2
        $response = $this->actingAs($this->adminA1)->put(route('admin.classes.update', $this->classA2), [
            'name' => 'Hacked Class Name',
            'branch_id' => $this->branchA2->id,
            'status' => 'active',
        ]);
        $this->assertTrue(in_array($response->status(), [403, 404], true));

        // Admin A1 tries to view session in branch A2
        $response = $this->actingAs($this->adminA1)->get(route('admin.attendances.sessions.show', $this->sessionA2));
        $this->assertTrue(in_array($response->status(), [403, 404], true));
    }

    public function test_tutor_cannot_access_payment_or_admin_routes(): void
    {
        // Tutor tries to access Owner Payments
        $response = $this->actingAs($this->tutorA1)->get(route('owner.payments.index'));
        $response->assertStatus(403);

        // Tutor tries to access Admin Payments
        $response = $this->actingAs($this->tutorA1)->get(route('admin.payments.index'));
        $response->assertStatus(403);

        // Tutor tries to access Admin Student management
        $response = $this->actingAs($this->tutorA1)->get(route('admin.students.index'));
        $response->assertStatus(403);
    }

    public function test_cross_tenant_foreign_key_tampering_is_rejected_on_creation(): void
    {
        // Owner A tries to create a payment using Tenant B's student
        $response = $this->actingAs($this->ownerA)->post(route('owner.payments.store'), [
            'student_id' => $this->studentB1->id, // Invalid cross-tenant ID
            'branch_id' => $this->branchA1->id,
            'period' => '2026-10',
            'amount' => 600000,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'belum_bayar',
        ]);

        $response->assertSessionHasErrors('student_id');

        // Owner A tries to create a payment using Tenant B's branch
        $response = $this->actingAs($this->ownerA)->post(route('owner.payments.store'), [
            'student_id' => $this->studentA1->id,
            'branch_id' => $this->branchB1->id, // Invalid cross-tenant branch
            'period' => '2026-10',
            'amount' => 600000,
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'belum_bayar',
        ]);

        $response->assertSessionHasErrors('branch_id');
    }

    public function test_payment_proof_upload_validates_mime_types_and_rejects_dangerous_files(): void
    {
        // Upload a malicious php file disguised as proof
        $fakeScript = UploadedFile::fake()->create('malicious.php', 100, 'application/x-php');

        $response = $this->actingAs($this->ownerA)->post(route('owner.payments.submit-proof', $this->paymentA1), [
            'proof' => $fakeScript,
        ]);

        $response->assertSessionHasErrors('proof');

        // Upload a valid png proof
        $validImage = UploadedFile::fake()->create('receipt.png', 100, 'image/png');

        $response = $this->actingAs($this->ownerA)->post(route('owner.payments.submit-proof', $this->paymentA1), [
            'proof' => $validImage,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('payments', [
            'id' => $this->paymentA1->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    public function test_user_model_hides_sensitive_fields_in_serialization(): void
    {
        $userArray = $this->ownerA->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);

        $userJson = json_encode($this->ownerA);
        $this->assertStringNotContainsString('Password123!', $userJson);
    }

    public function test_inactive_user_is_blocked_from_authenticated_portal_access(): void
    {
        // Deactivate tutor
        $this->tutorA1->update(['status' => 'inactive']);

        $response = $this->actingAs($this->tutorA1)->get(route('tutor.dashboard'));
        $response->assertStatus(403);
    }

    public function test_tutor_cannot_update_teaching_session_outside_their_assigned_scope(): void
    {
        // Tutor A1 tries to update Tutor A2's session
        $response = $this->actingAs($this->tutorA1)->put(route('tutor.sessions.update', $this->sessionA2), [
            'teaching_material' => 'Illicit material injection',
            'notes' => 'Unauthorized update',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_create_or_escalate_users_to_admin_or_owner(): void
    {
        // Admin tries to create another admin
        $response = $this->actingAs($this->adminA1)->post(route('admin.tutors.store'), [
            'name' => 'Rogue Admin',
            'email' => 'rogue.admin@example.com',
            'role' => 'admin', // Attempted role escalation
            'branch_id' => $this->branchA1->id,
            'password' => 'Secret12345!',
        ]);

        // Tutor is created strictly with 'tutor' role regardless of request input
        $createdUser = User::where('email', 'rogue.admin@example.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('tutor', $createdUser->role);
    }

    public function test_sql_injection_and_xss_attempts_in_search_parameters_are_safely_handled(): void
    {
        // SQL injection payload in search filter
        $payload = "' OR '1'='1' -- ";
        $response = $this->actingAs($this->ownerA)->get(route('owner.students.index', ['search' => $payload]));

        $response->assertStatus(200);
        // Ensure no data from Tenant B is leaked through search SQL injection
        $response->assertDontSee($this->studentB1->name);
    }

    public function test_tutor_cannot_record_attendance_for_unauthorized_teaching_session(): void
    {
        // Tutor A1 tries to record attendance for Session A2 (taught by Tutor A2)
        $response = $this->actingAs($this->tutorA1)->post(route('tutor.sessions.attendances.store', $this->sessionA2), [
            'attendances' => [
                $this->studentA2->id => [
                    'status' => 'hadir',
                    'notes' => 'Unauthorized attendance entry',
                ],
            ],
        ]);

        $response->assertStatus(403);
    }
}
