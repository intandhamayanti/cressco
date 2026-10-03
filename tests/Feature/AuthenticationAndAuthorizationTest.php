<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\HonorCalculation;
use App\Models\HonorScheme;
use App\Models\Payment;
use App\Models\Student;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\TutorAssignment;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthenticationAndAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenantA;

    private Tenant $tenantB;

    private Branch $branchA1;

    private Branch $branchA2;

    private Branch $branchB1;

    private User $ownerA;

    private User $adminA1;

    private User $tutorA;

    private User $superAdmin;

    private Student $studentA1;

    private Classes $classA1;

    private Classes $classA2;

    private TeachingSession $sessionA1;

    private Payment $paymentA1;

    private HonorScheme $schemeA1;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Tenant A
        $this->tenantA = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant A Bimbel',
            'slug' => 'tenant-a',
            'status' => 'active',
        ]);

        $this->branchA1 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Branch A1',
            'status' => 'active',
        ]);

        $this->branchA2 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Branch A2',
            'status' => 'active',
        ]);

        // 2. Tenant B
        $this->tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant B Bimbel',
            'slug' => 'tenant-b',
            'status' => 'active',
        ]);

        $this->branchB1 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Branch B1',
            'status' => 'active',
        ]);

        // 3. Users in Tenant A
        $this->ownerA = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Owner Tenant A',
            'email' => 'owner.a@example.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->adminA1 = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Admin A1',
            'email' => 'admin.a1@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Assign Admin A1 to Branch A1 ONLY (not Branch A2)
        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'user_id' => $this->adminA1->id,
        ]);

        $this->tutorA = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Tutor A',
            'email' => 'tutor.a@example.com',
            'password' => Hash::make('password'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        // 4. Super Admin
        $this->superAdmin = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => null,
            'name' => 'Super Admin',
            'email' => 'superadmin@platform.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        // 5. Operational Resources in Tenant A
        $this->studentA1 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Student A1',
            'status' => 'active',
        ]);

        $this->classA1 = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Class A1',
            'status' => 'active',
        ]);

        $this->classA2 = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'name' => 'Class A2 (Branch A2)',
            'status' => 'active',
        ]);

        // Assign Tutor A to Class A1 only
        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'tutor_id' => $this->tutorA->id,
            'class_id' => $this->classA1->id,
            'status' => 'active',
        ]);

        $this->sessionA1 = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'class_id' => $this->classA1->id,
            'scheduled_tutor_id' => $this->tutorA->id,
            'actual_tutor_id' => $this->tutorA->id,
            'session_date' => '2026-02-01',
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'status' => 'completed',
        ]);

        $this->paymentA1 = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'student_id' => $this->studentA1->id,
            'period' => '2026-02',
            'amount' => 500000.00,
            'due_date' => '2026-02-10',
            'status' => 'lunas',
            'recorded_by' => $this->adminA1->id,
        ]);

        $this->schemeA1 = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Scheme A1',
            'method' => 'per_session',
            'rate' => 100000.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $this->ownerA->id,
        ]);
    }

    // =========================================================================
    // 1. Authentication & Role Tests
    // =========================================================================

    public function test_guest_is_unauthorized_for_protected_routes(): void
    {
        $response = $this->getJson('/owner/ping');
        $response->assertStatus(401);

        $response = $this->get('/profile');
        $response->assertRedirect('/login');
    }

    public function test_role_is_retrieved_from_database_not_email(): void
    {
        $customUser = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Custom User',
            'email' => 'totally_owner_looking_email@bimbel.com', // Even with misleading email
            'password' => Hash::make('secret'),
            'role' => 'tutor', // DB says tutor!
            'status' => 'active',
        ]);

        $this->assertTrue($customUser->isTutor());
        $this->assertFalse($customUser->isOwner());
        $this->assertFalse($customUser->isAdmin());
        $this->assertTrue($customUser->hasRole('tutor'));
        $this->assertFalse($customUser->hasRole('owner'));
    }

    public function test_tenant_context_helper_resolves_authenticated_tenant(): void
    {
        $this->actingAs($this->ownerA);
        $this->assertTrue(TenantContext::isTenantUser());
        $this->assertEquals($this->tenantA->id, TenantContext::getTenantId());
        $this->assertEquals('Tenant A Bimbel', TenantContext::getTenant()->name);

        $this->actingAs($this->superAdmin);
        $this->assertFalse(TenantContext::isTenantUser());
        $this->assertNull(TenantContext::getTenantId());
    }

    // =========================================================================
    // 2. Tenant Isolation Tests (Cross-Tenant Denial)
    // =========================================================================

    public function test_user_cannot_access_another_tenants_resources(): void
    {
        // Student in Tenant B
        $studentB = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB1->id,
            'name' => 'Student B',
            'status' => 'active',
        ]);

        // Owner A tries to access Student in Tenant B -> MUST BE DENIED
        $this->assertFalse(Gate::forUser($this->ownerA)->allows('view', $studentB));
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('view', $studentB));
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('view', $studentB));

        // Branch in Tenant B -> MUST BE DENIED to Tenant A users
        $this->assertFalse(Gate::forUser($this->ownerA)->allows('view', $this->branchB1));
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('view', $this->branchB1));

        // Tenant entity itself
        $this->assertFalse(Gate::forUser($this->ownerA)->allows('view', $this->tenantB));
        $this->assertFalse(Gate::forUser($this->ownerA)->allows('update', $this->tenantB));
    }

    // =========================================================================
    // 3. Owner Authorization Tests
    // =========================================================================

    public function test_owner_has_full_access_to_all_branches_in_own_tenant(): void
    {
        // Owner has access to Branch A1 and Branch A2
        $this->assertTrue(Gate::forUser($this->ownerA)->allows('view', $this->branchA1));
        $this->assertTrue(Gate::forUser($this->ownerA)->allows('view', $this->branchA2));

        // Owner can access students and classes across all branches in tenant
        $this->assertTrue(Gate::forUser($this->ownerA)->allows('view', $this->studentA1));
        $this->assertTrue(Gate::forUser($this->ownerA)->allows('view', $this->classA1));
        $this->assertTrue(Gate::forUser($this->ownerA)->allows('view', $this->classA2));

        // Owner can manage branches
        $this->assertTrue(Gate::forUser($this->ownerA)->allows('create', Branch::class));
        $this->assertTrue(Gate::forUser($this->ownerA)->allows('update', $this->branchA1));

        // Owner can configure honor schemes and tenant settings
        $this->assertTrue(Gate::forUser($this->ownerA)->allows('create', HonorScheme::class));
        $this->assertTrue(Gate::forUser($this->ownerA)->allows('update', $this->schemeA1));

        $setting = TenantSetting::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'key' => 'due_day',
            'value' => ['day' => 5],
        ]);
        $this->assertTrue(Gate::forUser($this->ownerA)->allows('view', $setting));
        $this->assertTrue(Gate::forUser($this->ownerA)->allows('update', $setting));
    }

    // =========================================================================
    // 4. Admin Branch Scope Tests
    // =========================================================================

    public function test_admin_can_only_access_assigned_branch_and_resources(): void
    {
        // Admin A1 has access to Branch A1 (assigned via branch_user)
        $this->assertTrue(Gate::forUser($this->adminA1)->allows('view', $this->branchA1));
        $this->assertTrue(Gate::forUser($this->adminA1)->allows('view', $this->studentA1));
        $this->assertTrue(Gate::forUser($this->adminA1)->allows('view', $this->classA1));

        // Admin A1 DOES NOT have access to Branch A2 (unassigned)
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('view', $this->branchA2));
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('view', $this->classA2));

        // Student in Branch A2
        $studentA2 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'name' => 'Student A2',
            'status' => 'active',
        ]);
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('view', $studentA2));
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('update', $studentA2));
    }

    public function test_admin_explicit_restrictions(): void
    {
        // Admin CANNOT create, update or delete branches
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('create', Branch::class));
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('update', $this->branchA1));
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('delete', $this->branchA1));

        // Admin CANNOT configure honor schemes
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('create', HonorScheme::class));
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('update', $this->schemeA1));

        // Admin CANNOT view or update tenant settings
        $setting = TenantSetting::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'key' => 'currency',
            'value' => ['symbol' => 'Rp'],
        ]);
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('view', $setting));
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('update', $setting));

        // Admin CANNOT change role of self or others
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('updateRole', $this->adminA1));
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('updateBranchAccess', $this->adminA1));

        // Admin CANNOT create another Admin or Owner
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('create', [User::class, 'admin']));
        $this->assertFalse(Gate::forUser($this->adminA1)->allows('create', [User::class, 'owner']));

        // Admin CAN create Tutor
        $this->assertTrue(Gate::forUser($this->adminA1)->allows('create', [User::class, 'tutor']));
    }

    // =========================================================================
    // 5. Tutor Teaching Scope Tests
    // =========================================================================

    public function test_tutor_scope_is_assignment_based_not_generic_branch(): void
    {
        // Tutor does NOT have generic branch access
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('view', $this->branchA1));

        // Tutor CAN access Class A1 (assigned)
        $this->assertTrue(Gate::forUser($this->tutorA)->allows('view', $this->classA1));

        // Tutor CANNOT access Class A2 (not assigned)
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('view', $this->classA2));

        // Tutor CAN access teaching session A1 (assigned)
        $this->assertTrue(Gate::forUser($this->tutorA)->allows('view', $this->sessionA1));
        $this->assertTrue(Gate::forUser($this->tutorA)->allows('recordAttendance', $this->sessionA1));

        // Session for unassigned class
        $sessionA2 = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'class_id' => $this->classA2->id,
            'scheduled_tutor_id' => $this->ownerA->id,
            'session_date' => '2026-02-05',
            'start_time' => '10:00:00',
            'end_time' => '11:30:00',
            'status' => 'scheduled',
        ]);
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('view', $sessionA2));
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('recordAttendance', $sessionA2));
    }

    public function test_tutor_explicit_restrictions(): void
    {
        // Tutor CANNOT access payments
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('viewAny', Payment::class));
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('view', $this->paymentA1));
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('create', Payment::class));

        // Tutor CANNOT configure honor schemes
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('viewAny', HonorScheme::class));
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('create', HonorScheme::class));

        // Tutor CANNOT manage users
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('create', User::class));
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('delete', $this->adminA1));

        // Tutor CANNOT replace tutor
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('replaceTutor', $this->sessionA1));

        // Tutor CANNOT generate honor calculations
        $this->assertFalse(Gate::forUser($this->tutorA)->allows('create', HonorCalculation::class));
    }

    // =========================================================================
    // 6. Super Admin Platform Scope Tests
    // =========================================================================

    public function test_super_admin_has_null_tenant_id_and_platform_scope(): void
    {
        $this->assertNull($this->superAdmin->tenant_id);
        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertFalse($this->superAdmin->isTenantUser());

        // Super Admin can access platform/all tenant policies
        $this->assertTrue(Gate::forUser($this->superAdmin)->allows('view', $this->tenantA));
        $this->assertTrue(Gate::forUser($this->superAdmin)->allows('view', $this->tenantB));
        $this->assertTrue(Gate::forUser($this->superAdmin)->allows('view', $this->branchA1));
        $this->assertTrue(Gate::forUser($this->superAdmin)->allows('view', $this->branchB1));
    }

    // =========================================================================
    // 7. Route & Middleware Protection Tests
    // =========================================================================

    public function test_route_middleware_enforces_role_protection(): void
    {
        // Owner route
        $this->actingAs($this->ownerA)->getJson('/owner/ping')->assertOk();
        $this->actingAs($this->adminA1)->getJson('/owner/ping')->assertForbidden();
        $this->actingAs($this->tutorA)->getJson('/owner/ping')->assertForbidden();

        // Admin route
        $this->actingAs($this->adminA1)->getJson('/admin/ping')->assertOk();
        $this->actingAs($this->tutorA)->getJson('/admin/ping')->assertForbidden();

        // Tutor route
        $this->actingAs($this->tutorA)->getJson('/tutor/ping')->assertOk();
        $this->actingAs($this->adminA1)->getJson('/tutor/ping')->assertForbidden();

        // Super Admin route
        $this->actingAs($this->superAdmin)->getJson('/super-admin/ping')->assertOk();
        $this->actingAs($this->ownerA)->getJson('/super-admin/ping')->assertForbidden();

        // Combined role route
        $this->actingAs($this->ownerA)->getJson('/tenant-ops/ping')->assertOk();
        $this->actingAs($this->adminA1)->getJson('/tenant-ops/ping')->assertOk();
        $this->actingAs($this->tutorA)->getJson('/tenant-ops/ping')->assertForbidden();
    }

    public function test_tenant_middleware_rejects_inactive_or_missing_tenant(): void
    {
        $inactiveTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Inactive Tenant',
            'slug' => 'inactive-tenant',
            'status' => 'suspended',
        ]);

        $inactiveUser = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $inactiveTenant->id,
            'name' => 'Suspended Owner',
            'email' => 'suspended@example.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->actingAs($inactiveUser)->getJson('/owner/ping')->assertForbidden();
    }
}
