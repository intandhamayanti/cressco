<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_tutor_bambang_can_authenticate_with_password123(): void
    {
        $tutor = User::factory()->tutor()->create([
            'email' => 'tutor.bambang@cressco.test',
            'password' => 'Password123!',
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', 'tutor.bambang@cressco.test')
            ->set('form.password', 'Password123!');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($tutor);
    }

    public function test_owner_can_authenticate_with_password123(): void
    {
        $owner = User::factory()->owner()->create([
            'email' => 'owner@cressco.test',
            'password' => 'Password123!',
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', 'owner@cressco.test')
            ->set('form.password', 'Password123!');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($owner);
    }

    public function test_admin_can_authenticate_with_password123(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin.malang@cressco.test',
            'password' => 'Password123!',
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', 'admin.malang@cressco.test')
            ->set('form.password', 'Password123!');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_authentication_with_wrong_password_shows_credential_error_not_password_required(): void
    {
        $user = User::factory()->create([
            'email' => 'tutor.bambang@cressco.test',
            'password' => 'Password123!',
        ]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', 'tutor.bambang@cressco.test')
            ->set('form.password', 'WrongPassword!');

        $component->call('login');

        $component
            ->assertHasErrors(['form.email'])
            ->assertHasNoErrors(['form.password'])
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_empty_password_shows_password_required_validation_error(): void
    {
        $component = Volt::test('pages.auth.login')
            ->set('form.email', 'tutor.bambang@cressco.test')
            ->set('form.password', '');

        $component->call('login');

        $component
            ->assertHasErrors(['form.password'])
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_navigation_menu_can_be_rendered(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response
            ->assertOk()
            ->assertSeeVolt('layout.navigation');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('layout.navigation');

        $component->call('logout');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
