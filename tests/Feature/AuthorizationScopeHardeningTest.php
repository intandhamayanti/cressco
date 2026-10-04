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
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationScopeHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Branch $branchA1;

    protected Branch $branchA2;

    protected Branch $branchB1;

    protected User $ownerA;

    protected User $ownerB;

    protected User $adminA1; // Access to branchA1 only

    protected User $tutorA1; // Assigned to classA1 only

    protected User $tutorA2; // Assigned to classA2 only

    protected Classes $classA1;

    protected Classes $classA2;

    protected Classes $classB1;

    protected Student $studentA1;

    protected Student $studentA2;

    protected Student $studentB1;

    protected TeachingSession $sessionA1;

    protected TeachingSession $sessionA2;

    protected Payment $paymentA1;

    protected Payment $paymentA2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tenants
        $this->tenantA = Tenant::create([
            'name' => 'Bimbel Alpha',
            'slug' => 'bimbel-alpha',
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'Bimbel Beta',
            'slug' => 'bimbel-beta',
            'status' => 'active',
        ]);

        // 2. Branches
        $this->branchA1 = Branch::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Alpha Jakarta',
            'code' => 'ALP-JKT',
            'status' => 'active',
        ]);

        $this->branchA2 = Branch::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Alpha Bandung',
            'code' => 'ALP-BDG',
            'status' => 'active',
        ]);

        $this->branchB1 = Branch::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Beta Surabaya',
            'code' => 'BET-SBY',
            'status' => 'active',
        ]);

        // 3. Users
        $this->ownerA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Owner Alpha',
            'email' => 'owner.alpha@test.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->ownerB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Owner Beta',
            'email' => 'owner.beta@test.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->adminA1 = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Admin Alpha JKT',
            'email' => 'admin.jkt@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);
        // Attach Admin to branchA1 only
        BranchUser::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'user_id' => $this->adminA1->id,
        ]);

        $this->tutorA1 = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Tutor Alpha Math',
            'email' => 'tutor.math@test.com',
            'password' => bcrypt('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->tutorA2 = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Tutor Alpha Physics',
            'email' => 'tutor.physics@test.com',
            'password' => bcrypt('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        // 4. Classes
        $this->classA1 = Classes::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Matematika 12 IPA',
            'subject' => 'Matematika',
            'level' => '12 SMA',
            'capacity' => 20,
            'price_per_month' => 500000,
            'status' => 'active',
        ]);

        $this->classA2 = Classes::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'name' => 'Fisika 12 IPA Bandung',
            'subject' => 'Fisika',
            'level' => '12 SMA',
            'capacity' => 20,
            'price_per_month' => 500000,
            'status' => 'active',
        ]);

        $this->classB1 = Classes::create([
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB1->id,
            'name' => 'Kimia 12 Beta SBY',
            'subject' => 'Kimia',
            'level' => '12 SMA',
            'capacity' => 20,
            'price_per_month' => 500000,
            'status' => 'active',
        ]);

        // Assign Tutor A1 to Class A1
        TutorAssignment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'class_id' => $this->classA1->id,
            'tutor_id' => $this->tutorA1->id,
            'started_at' => now()->subMonth(),
            'status' => 'active',
        ]);

        // Assign Tutor A2 to Class A2
        TutorAssignment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'class_id' => $this->classA2->id,
            'tutor_id' => $this->tutorA2->id,
            'started_at' => now()->subMonth(),
            'status' => 'active',
        ]);

        // 5. Students & Enrollments
        $this->studentA1 = Student::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Budi Santoso JKT',
            'email' => 'budi@test.com',
            'phone' => '081234567891',
            'status' => 'active',
        ]);

        Enrollment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'class_id' => $this->classA1->id,
            'student_id' => $this->studentA1->id,
            'started_at' => now()->subMonth(),
            'status' => 'active',
        ]);

        $this->studentA2 = Student::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'name' => 'Siti Bandung',
            'email' => 'siti@test.com',
            'phone' => '081234567892',
            'status' => 'active',
        ]);

        Enrollment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'class_id' => $this->classA2->id,
            'student_id' => $this->studentA2->id,
            'started_at' => now()->subMonth(),
            'status' => 'active',
        ]);

        $this->studentB1 = Student::create([
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB1->id,
            'name' => 'Rina Surabaya Beta',
            'email' => 'rina@test.com',
            'phone' => '081234567893',
            'status' => 'active',
        ]);

        // 6. Teaching Sessions
        $this->sessionA1 = TeachingSession::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'class_id' => $this->classA1->id,
            'scheduled_tutor_id' => $this->tutorA1->id,
            'session_date' => now()->toDateString(),
            'start_time' => '16:00',
            'end_time' => '17:30',
            'status' => 'scheduled',
        ]);

        $this->sessionA2 = TeachingSession::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'class_id' => $this->classA2->id,
            'scheduled_tutor_id' => $this->tutorA2->id,
            'session_date' => now()->toDateString(),
            'start_time' => '18:00',
            'end_time' => '19:30',
            'status' => 'scheduled',
        ]);

        // 7. Payments
        $this->paymentA1 = Payment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'student_id' => $this->studentA1->id,
            'period' => '2026-10',
            'amount' => 500000,
            'due_date' => now()->addDays(7),
            'status' => 'belum_bayar',
            'recorded_by' => $this->ownerA->id,
        ]);

        $this->paymentA2 = Payment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'student_id' => $this->studentA2->id,
            'period' => '2026-10',
            'amount' => 500000,
            'due_date' => now()->addDays(7),
            'status' => 'belum_bayar',
            'recorded_by' => $this->ownerA->id,
        ]);
    }

    // ==========================================
    // 1. Cross-Tenant Isolation Tests
    // ==========================================

    public function test_owner_cannot_access_or_view_other_tenant_resources(): void
    {
        // Owner A cannot view Beta's branch (tenant boundary)
        $res = $this->actingAs($this->ownerA)->get('/owner/branches/'.$this->branchB1->id);
        $res->assertNotFound();

        // Owner A cannot view Beta's class
        $res = $this->actingAs($this->ownerA)->get('/owner/classes/'.$this->classB1->id);
        $res->assertNotFound();

        // Owner A cannot view Beta's student
        $res = $this->actingAs($this->ownerA)->get('/owner/students/'.$this->studentB1->id);
        $res->assertNotFound();
    }

    public function test_admin_cannot_access_other_tenant_resources(): void
    {
        // Admin A1 cannot access Beta's student
        $res = $this->actingAs($this->adminA1)->get('/admin/students/'.$this->studentB1->id);
        $res->assertNotFound();

        // Admin A1 cannot access Beta's class
        $res = $this->actingAs($this->adminA1)->get('/admin/classes/'.$this->classB1->id);
        $res->assertNotFound();
    }

    public function test_tutor_cannot_access_other_tenant_classes_or_sessions(): void
    {
        // Tutor A1 cannot view Beta's class
        $res = $this->actingAs($this->tutorA1)->get('/tutor/classes/'.$this->classB1->id);
        $res->assertNotFound();
    }

    // ==========================================
    // 2. Cross-Role Route Protection Tests
    // ==========================================

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/owner/dashboard')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/tutor/dashboard')->assertRedirect('/login');
    }

    public function test_admin_cannot_access_owner_or_tutor_routes(): void
    {
        $this->actingAs($this->adminA1)->get('/owner/dashboard')->assertForbidden();
        $this->actingAs($this->adminA1)->get('/owner/branches')->assertForbidden();
        $this->actingAs($this->adminA1)->get('/owner/users')->assertForbidden();
        $this->actingAs($this->adminA1)->get('/owner/settings')->assertForbidden();
        $this->actingAs($this->adminA1)->get('/tutor/dashboard')->assertForbidden();
    }

    public function test_tutor_cannot_access_owner_or_admin_routes(): void
    {
        $this->actingAs($this->tutorA1)->get('/owner/dashboard')->assertForbidden();
        $this->actingAs($this->tutorA1)->get('/owner/branches')->assertForbidden();
        $this->actingAs($this->tutorA1)->get('/owner/payments')->assertForbidden();
        $this->actingAs($this->tutorA1)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($this->tutorA1)->get('/admin/students')->assertForbidden();
        $this->actingAs($this->tutorA1)->get('/admin/payments')->assertForbidden();
    }

    public function test_owner_cannot_access_admin_or_tutor_exclusive_routes(): void
    {
        $this->actingAs($this->ownerA)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($this->ownerA)->get('/tutor/dashboard')->assertForbidden();
    }

    // ==========================================
    // 3. Admin Branch Isolation Tests
    // ==========================================

    public function test_admin_cannot_access_unassigned_branch_resources(): void
    {
        // Admin A1 is only assigned to branchA1 (Jakarta). Cannot view class in branchA2 (Bandung).
        $res = $this->actingAs($this->adminA1)->get('/admin/classes/'.$this->classA2->id);
        $res->assertNotFound();

        // Cannot view student in branchA2
        $res = $this->actingAs($this->adminA1)->get('/admin/students/'.$this->studentA2->id);
        $res->assertNotFound();

        // Cannot view payment in branchA2
        $res = $this->actingAs($this->adminA1)->get('/admin/payments/'.$this->paymentA2->id);
        $res->assertForbidden();

        // Cannot view session in branchA2
        $res = $this->actingAs($this->adminA1)->get('/admin/attendances/sessions/'.$this->sessionA2->id);
        $res->assertNotFound();
    }

    public function test_admin_cannot_create_student_in_unassigned_branch(): void
    {
        $res = $this->actingAs($this->adminA1)->post('/admin/students', [
            'branch_id' => $this->branchA2->id, // Unassigned branch
            'name' => 'Illegal Student',
            'email' => 'illegal@test.com',
            'phone' => '08129999999',
            'status' => 'active',
        ]);

        $res->assertSessionHasErrors('branch_id');
        $this->assertDatabaseMissing('students', ['name' => 'Illegal Student']);
    }

    public function test_admin_cannot_create_payment_for_unassigned_branch(): void
    {
        $res = $this->actingAs($this->adminA1)->post('/admin/payments', [
            'student_id' => $this->studentA2->id,
            'branch_id' => $this->branchA2->id, // Unassigned branch
            'period' => '2026-10',
            'amount' => 500000,
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'belum_bayar',
        ]);

        $res->assertSessionHasErrors('branch_id');
    }

    // ==========================================
    // 4. Tutor Teaching Scope Isolation Tests
    // ==========================================

    public function test_tutor_cannot_access_unassigned_classes_or_sessions(): void
    {
        // Tutor A1 (assigned to Class A1) cannot view Class A2
        $res = $this->actingAs($this->tutorA1)->get('/tutor/classes/'.$this->classA2->id);
        $res->assertForbidden();

        // Tutor A1 cannot view Session A2
        $res = $this->actingAs($this->tutorA1)->get('/tutor/sessions/'.$this->sessionA2->id);
        $res->assertNotFound();

        // Tutor A1 cannot update Session A2 material
        $res = $this->actingAs($this->tutorA1)->put('/tutor/sessions/'.$this->sessionA2->id, [
            'material' => 'Illegal Update',
        ]);
        $res->assertForbidden();
    }

    public function test_tutor_can_access_assigned_classes_and_sessions(): void
    {
        // Tutor A1 can view Class A1
        $res = $this->actingAs($this->tutorA1)->get('/tutor/classes/'.$this->classA1->id);
        $res->assertOk();

        // Tutor A1 can view Session A1
        $res = $this->actingAs($this->tutorA1)->get('/tutor/sessions/'.$this->sessionA1->id);
        $res->assertOk();

        // Tutor A1 can update Session A1 material
        $res = $this->actingAs($this->tutorA1)->put('/tutor/sessions/'.$this->sessionA1->id, [
            'material' => 'Bab 1 Aljabar Linier',
            'notes' => 'Siswa sangat aktif berdiskusi',
        ]);
        $res->assertRedirect();
        $this->assertDatabaseHas('teaching_sessions', [
            'id' => $this->sessionA1->id,
            'material' => 'Bab 1 Aljabar Linier',
        ]);
    }

    // ==========================================
    // 5. Inactive Account & Suspended Tenant Tests
    // ==========================================

    public function test_inactive_user_cannot_access_portal(): void
    {
        $inactiveTutor = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Inactive Tutor',
            'email' => 'inactive.tutor@test.com',
            'password' => bcrypt('password'),
            'role' => 'tutor',
            'status' => 'inactive',
        ]);

        $this->actingAs($inactiveTutor)->get('/tutor/dashboard')->assertForbidden();
        $this->actingAs($inactiveTutor)->get('/tutor/classes')->assertForbidden();
    }

    public function test_user_in_suspended_tenant_cannot_access_portal(): void
    {
        $suspendedTenant = Tenant::create([
            'name' => 'Suspended Bimbel',
            'slug' => 'suspended-bimbel',
            'status' => 'suspended',
        ]);

        $ownerSuspended = User::create([
            'tenant_id' => $suspendedTenant->id,
            'name' => 'Suspended Owner',
            'email' => 'suspended.owner@test.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->actingAs($ownerSuspended)->get('/owner/dashboard')->assertForbidden();
        $this->actingAs($ownerSuspended)->get('/owner/branches')->assertForbidden();
    }

    // ==========================================
    // 6. Privilege Escalation & Policy Guards
    // ==========================================

    public function test_user_cannot_deactivate_or_delete_themselves(): void
    {
        // Owner attempting to toggle own status is redirected with error
        $res = $this->actingAs($this->ownerA)->patch('/owner/users/'.$this->ownerA->id.'/toggle-status');
        $res->assertSessionHas('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        $this->assertEquals('active', $this->ownerA->fresh()->status);

        // UserPolicy returns false for self-deletion
        $this->assertFalse(Gate::forUser($this->ownerA)->allows('delete', $this->ownerA));
    }

    public function test_owner_has_access_across_all_branches_in_their_tenant(): void
    {
        // Owner A can view Class A1 (Jakarta) and Class A2 (Bandung)
        $this->actingAs($this->ownerA)->get('/owner/classes/'.$this->classA1->id)->assertOk();
        $this->actingAs($this->ownerA)->get('/owner/classes/'.$this->classA2->id)->assertOk();

        // Owner A can view Student A1 and Student A2
        $this->actingAs($this->ownerA)->get('/owner/students/'.$this->studentA1->id)->assertOk();
        $this->actingAs($this->ownerA)->get('/owner/students/'.$this->studentA2->id)->assertOk();

        // Owner A can view Payment A1 and Payment A2
        $this->actingAs($this->ownerA)->get('/owner/payments/'.$this->paymentA1->id)->assertOk();
        $this->actingAs($this->ownerA)->get('/owner/payments/'.$this->paymentA2->id)->assertOk();
    }
}
