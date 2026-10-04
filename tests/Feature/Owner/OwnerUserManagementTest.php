<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $owner;

    protected Branch $branchMalang;

    protected Branch $branchMakassar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Tutoring Center',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->owner = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Budi Pratama',
            'email' => 'owner@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'owner',
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
    }

    public function test_owner_can_list_users(): void
    {
        $admin = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Siti',
            'email' => 'admin.siti@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $tutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tutor Ahmad',
            'email' => 'tutor.ahmad@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/users');

        $response->assertOk();
        $response->assertViewIs('owner.users.index');
        $response->assertSee('Admin Siti');
        $response->assertSee('Tutor Ahmad');
        $response->assertSee('Budi Pratama');
    }

    public function test_owner_cannot_see_other_tenant_users(): void
    {
        $otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Lain',
            'slug' => 'tenant-lain',
            'status' => 'active',
        ]);

        $otherUser = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'User Tenant Lain',
            'email' => 'user@other.test',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/users');

        $response->assertOk();
        $response->assertDontSee('User Tenant Lain');
        $response->assertDontSee('user@other.test');
    }

    public function test_owner_can_create_admin_with_branch_access(): void
    {
        $response = $this->actingAs($this->owner)->post('/owner/users', [
            'name' => 'Admin Baru',
            'email' => 'admin.baru@primeacademy.test',
            'role' => 'admin',
            'password' => 'Password123!',
            'status' => 'active',
            'branch_ids' => [$this->branchMalang->id, $this->branchMakassar->id],
        ]);

        $response->assertRedirect('/owner/users');
        $response->assertSessionHas('success');

        $createdUser = User::where('email', 'admin.baru@primeacademy.test')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('Admin Baru', $createdUser->name);
        $this->assertEquals('admin', $createdUser->role);
        $this->assertEquals($this->tenant->id, $createdUser->tenant_id);

        // Check branch assignments
        $this->assertEquals(2, $createdUser->branches()->count());
        $this->assertTrue($createdUser->hasBranchAccess($this->branchMalang->id));
        $this->assertTrue($createdUser->hasBranchAccess($this->branchMakassar->id));
    }

    public function test_owner_can_create_tutor(): void
    {
        $response = $this->actingAs($this->owner)->post('/owner/users', [
            'name' => 'Tutor Baru',
            'email' => 'tutor.baru@primeacademy.test',
            'role' => 'tutor',
            'password' => 'Password123!',
            'status' => 'active',
        ]);

        $response->assertRedirect('/owner/users');
        $response->assertSessionHas('success');

        $createdUser = User::where('email', 'tutor.baru@primeacademy.test')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('tutor', $createdUser->role);
    }

    public function test_owner_cannot_create_super_admin_or_invalid_role(): void
    {
        $response = $this->actingAs($this->owner)->post('/owner/users', [
            'name' => 'Hacker Admin',
            'email' => 'hacker@primeacademy.test',
            'role' => 'super_admin',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors(['role']);
        $this->assertDatabaseMissing('users', ['email' => 'hacker@primeacademy.test']);
    }

    public function test_owner_can_view_user_details(): void
    {
        $admin = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Detail',
            'email' => 'admin.detail@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($this->owner)->get("/owner/users/{$admin->id}");

        $response->assertOk();
        $response->assertViewIs('owner.users.show');
        $response->assertSee('Admin Detail');
        $response->assertSee('Prime Academy - Malang');
    }

    public function test_owner_cannot_view_other_tenant_user_details(): void
    {
        $otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Lain',
            'slug' => 'tenant-lain',
            'status' => 'active',
        ]);

        $otherUser = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Other User',
            'email' => 'other@tenant.test',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get("/owner/users/{$otherUser->id}");
        $response->assertNotFound();
    }

    public function test_owner_can_update_user_and_sync_branches(): void
    {
        $admin = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Awal',
            'email' => 'admin.awal@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'user_id' => $admin->id,
        ]);

        $response = $this->actingAs($this->owner)->put("/owner/users/{$admin->id}", [
            'name' => 'Admin Diubah',
            'email' => 'admin.diubah@primeacademy.test',
            'role' => 'admin',
            'status' => 'active',
            'branch_ids' => [$this->branchMakassar->id],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $admin->refresh();
        $this->assertEquals('Admin Diubah', $admin->name);
        $this->assertEquals('admin.diubah@primeacademy.test', $admin->email);
        $this->assertEquals(1, $admin->branches()->count());
        $this->assertTrue($admin->hasBranchAccess($this->branchMakassar->id));
        $this->assertFalse($admin->hasBranchAccess($this->branchMalang->id));
    }

    public function test_owner_can_toggle_user_status(): void
    {
        $admin = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Toggle',
            'email' => 'admin.toggle@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->patch("/owner/users/{$admin->id}/toggle-status");

        $response->assertRedirect();
        $admin->refresh();
        $this->assertEquals('inactive', $admin->status);

        // Toggle back
        $response2 = $this->actingAs($this->owner)->patch("/owner/users/{$admin->id}/toggle-status");
        $response2->assertRedirect();
        $admin->refresh();
        $this->assertEquals('active', $admin->status);
    }

    public function test_owner_cannot_deactivate_self(): void
    {
        $response = $this->actingAs($this->owner)->patch("/owner/users/{$this->owner->id}/toggle-status");

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->owner->refresh();
        $this->assertEquals('active', $this->owner->status);
    }

    public function test_user_search_and_role_filtering(): void
    {
        User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Bambang Sudirman',
            'email' => 'bambang@primeacademy.test',
            'password' => bcrypt('Password123!'),
            'role' => 'tutor',
            'status' => 'inactive',
        ]);

        // Search by query
        $resSearch = $this->actingAs($this->owner)->get('/owner/users?search=Sudirman');
        $resSearch->assertOk();
        $resSearch->assertSee('Bambang Sudirman');
        $resSearch->assertDontSee('Siti Nurhaliza');

        // Filter by role
        $resRole = $this->actingAs($this->owner)->get('/owner/users?role=admin');
        $resRole->assertOk();
        $resRole->assertSee('Siti Nurhaliza');
        $resRole->assertDontSee('Bambang Sudirman');

        // Filter by status
        $resStatus = $this->actingAs($this->owner)->get('/owner/users?status=inactive');
        $resStatus->assertOk();
        $resStatus->assertSee('Bambang Sudirman');
        $resStatus->assertDontSee('Siti Nurhaliza');
    }
}
