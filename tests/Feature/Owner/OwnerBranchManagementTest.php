<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerBranchManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $owner;

    protected Branch $branchMalang;

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
            'address' => 'Jl. Ijen No. 12, Malang',
            'phone' => '0341-551234',
            'status' => 'active',
        ]);
    }

    public function test_owner_can_list_branches(): void
    {
        $response = $this->actingAs($this->owner)->get('/owner/branches');

        $response->assertOk();
        $response->assertViewIs('owner.branches.index');
        $response->assertSee('Prime Academy - Malang');
        $response->assertSee('MLG-01');
    }

    public function test_owner_cannot_see_other_tenant_branches(): void
    {
        $otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bimbel Lain',
            'slug' => 'bimbel-lain',
            'status' => 'active',
        ]);

        $otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Cabang Surabaya Rahasia',
            'code' => 'SBY-99',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/branches');

        $response->assertOk();
        $response->assertDontSee('Cabang Surabaya Rahasia');
        $response->assertDontSee('SBY-99');
    }

    public function test_owner_can_create_a_new_branch(): void
    {
        $response = $this->actingAs($this->owner)->post('/owner/branches', [
            'name' => 'Prime Academy - Bandung',
            'code' => 'BDG-01',
            'address' => 'Jl. Dago No. 24, Bandung',
            'phone' => '022-4231122',
            'status' => 'active',
        ]);

        $response->assertRedirect('/owner/branches');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Prime Academy - Bandung',
            'code' => 'BDG-01',
            'status' => 'active',
        ]);
    }

    public function test_branch_code_must_be_unique_within_same_tenant(): void
    {
        $response = $this->actingAs($this->owner)->post('/owner/branches', [
            'name' => 'Duplikat Cabang',
            'code' => 'MLG-01', // Already exists in this tenant
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_owner_can_view_branch_details(): void
    {
        $response = $this->actingAs($this->owner)->get('/owner/branches/'.$this->branchMalang->id);

        $response->assertOk();
        $response->assertViewIs('owner.branches.show');
        $response->assertSee('Prime Academy - Malang');
        $response->assertSee('MLG-01');
        $response->assertSee('Jl. Ijen No. 12, Malang');
    }

    public function test_owner_cannot_view_other_tenant_branch_details(): void
    {
        $otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bimbel Lain',
            'slug' => 'bimbel-lain',
            'status' => 'active',
        ]);

        $otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Cabang Rahasia',
            'code' => 'RHS-01',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->owner)->get('/owner/branches/'.$otherBranch->id);

        $response->assertNotFound();
    }

    public function test_owner_can_update_branch(): void
    {
        $response = $this->actingAs($this->owner)->put('/owner/branches/'.$this->branchMalang->id, [
            'name' => 'Prime Academy - Malang Pusat',
            'code' => 'MLG-01-PST',
            'address' => 'Jl. Ijen No. 14, Malang Baru',
            'phone' => '0341-999888',
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'id' => $this->branchMalang->id,
            'name' => 'Prime Academy - Malang Pusat',
            'code' => 'MLG-01-PST',
            'address' => 'Jl. Ijen No. 14, Malang Baru',
        ]);
    }

    public function test_owner_can_toggle_branch_status(): void
    {
        $this->assertEquals('active', $this->branchMalang->status);

        $response = $this->actingAs($this->owner)->patch('/owner/branches/'.$this->branchMalang->id.'/toggle-status');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->branchMalang->refresh();
        $this->assertEquals('inactive', $this->branchMalang->status);

        // Toggle back to active
        $this->actingAs($this->owner)->patch('/owner/branches/'.$this->branchMalang->id.'/toggle-status');
        $this->branchMalang->refresh();
        $this->assertEquals('active', $this->branchMalang->status);
    }

    public function test_owner_branch_search_and_status_filtering(): void
    {
        Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Prime Academy - Makassar',
            'code' => 'MKS-01',
            'status' => 'inactive',
        ]);

        // Search by keyword
        $response = $this->actingAs($this->owner)->get('/owner/branches?search=Makassar');
        $response->assertOk();
        $response->assertSee('Prime Academy - Makassar');
        $response->assertDontSee('Prime Academy - Malang');

        // Filter by inactive status
        $response2 = $this->actingAs($this->owner)->get('/owner/branches?status=inactive');
        $response2->assertOk();
        $response2->assertSee('Prime Academy - Makassar');
        $response2->assertDontSee('Prime Academy - Malang');
    }
}
