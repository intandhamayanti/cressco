<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerSettingTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $owner;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Surabaya',
            'slug' => 'prime-academy',
            'email' => 'info@primeacademy.com',
            'phone' => '081234567890',
            'address' => 'Jl. Pemuda No. 45, Surabaya',
            'status' => 'active',
        ]);

        $this->owner = User::factory()->owner()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Ethan Walker',
            'email' => 'ethan@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Surabaya Pusat',
            'code' => 'SBY',
            'city' => 'Surabaya',
            'status' => 'active',
        ]);
    }

    public function test_owner_can_view_settings_and_profile_page(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('owner.settings'));

        $response->assertOk();
        $response->assertSee('Setting');
        $response->assertSee('Personal Information');
        $response->assertSee('Ethan Walker');
        $response->assertSee('ethan@gmail.com');
        $response->assertSee('Prime Academy Surabaya');
        $response->assertSee('Job Information');
        $response->assertSee('General Setting');
    }

    public function test_owner_can_update_profile_information(): void
    {
        $response = $this->actingAs($this->owner)
            ->put(route('owner.settings.profile'), [
                'name' => 'Ethan Walker Updated',
                'email' => 'ethan.new@gmail.com',
            ]);

        $response->assertRedirect(route('owner.settings'));
        $this->owner->refresh();

        $this->assertEquals('Ethan Walker Updated', $this->owner->name);
        $this->assertEquals('ethan.new@gmail.com', $this->owner->email);
    }

    public function test_owner_can_change_password(): void
    {
        $response = $this->actingAs($this->owner)
            ->put(route('owner.settings.password'), [
                'current_password' => 'password123',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('owner.settings'));
        $this->owner->refresh();

        $this->assertTrue(Hash::check('newpassword123', $this->owner->password));
    }

    public function test_owner_cannot_change_password_with_incorrect_current_password(): void
    {
        $response = $this->actingAs($this->owner)
            ->put(route('owner.settings.password'), [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
        $this->owner->refresh();

        $this->assertTrue(Hash::check('password123', $this->owner->password));
    }

    public function test_owner_can_update_tenant_settings_and_preferences(): void
    {
        $response = $this->actingAs($this->owner)
            ->put(route('owner.settings.tenant'), [
                'name' => 'Prime Academy Indonesia',
                'email' => 'contact@primeacademy.id',
                'phone' => '08999999999',
                'address' => 'Gedung Edukasi Lt. 3, Surabaya',
                'description' => 'Bimbel Terkemuka Indonesia',
                'notifications_enabled' => true,
                'language' => 'id',
            ]);

        $response->assertRedirect(route('owner.settings'));
        $this->tenant->refresh();

        $this->assertEquals('Prime Academy Indonesia', $this->tenant->name);
        $this->assertEquals('contact@primeacademy.id', $this->tenant->email);
        $this->assertEquals('08999999999', $this->tenant->phone);
        $this->assertEquals('Gedung Edukasi Lt. 3, Surabaya', $this->tenant->address);

        $this->assertDatabaseHas('tenant_settings', [
            'tenant_id' => $this->tenant->id,
            'key' => 'general_settings',
        ]);
    }

    public function test_profile_route_redirects_owner_to_settings(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('profile'));

        $response->assertRedirect(route('owner.settings'));
    }

    public function test_owner_can_logout(): void
    {
        $response = $this->actingAs($this->owner)
            ->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
