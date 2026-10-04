<?php

namespace Tests\Feature\Tutor;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TutorProfileTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Tenant $otherTenant;

    protected Branch $branchMalang;

    protected User $tutor;

    protected User $otherTutor;

    protected User $adminUser;

    protected Classes $classMath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Tutoring',
            'slug' => 'prime-academy',
            'domain' => 'prime.cressco.test',
            'status' => 'active',
        ]);

        $this->otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Nexus Bimbel Edu',
            'slug' => 'nexus-edu',
            'domain' => 'nexus.cressco.test',
            'status' => 'active',
        ]);

        $this->branchMalang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Suhat Malang',
            'code' => 'MLG-01',
            'address' => 'Jl. Soekarno Hatta No. 12, Malang',
            'status' => 'active',
        ]);

        $this->tutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Budi Satria Tutor',
            'email' => 'budi.tutor@prime.test',
            'password' => Hash::make('password123'),
            'role' => 'tutor',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $this->otherTutor = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Dewi Lestari Tutor',
            'email' => 'dewi.tutor@prime.test',
            'password' => Hash::make('password123'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->adminUser = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Operasional',
            'email' => 'admin@prime.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->classMath = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'name' => '12 SMA Matematika IPA',
            'subject' => 'Matematika',
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'tutor_id' => $this->tutor->id,
            'started_at' => Carbon::now()->subMonths(2),
            'status' => 'active',
        ]);

        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branchMalang->id,
            'class_id' => $this->classMath->id,
            'scheduled_tutor_id' => $this->tutor->id,
            'actual_tutor_id' => $this->tutor->id,
            'session_date' => Carbon::yesterday()->format('Y-m-d'),
            'start_time' => '14:00',
            'end_time' => '16:00',
            'status' => 'completed',
            'topic' => 'Integral Parsial',
        ]);
    }

    public function test_guest_cannot_access_tutor_profile(): void
    {
        $this->get(route('tutor.profile'))->assertRedirect(route('login'));
        $this->put(route('tutor.profile.update'), ['name' => 'Test', 'email' => 'test@prime.test'])->assertRedirect(route('login'));
        $this->put(route('tutor.profile.password'), ['current_password' => 'password123', 'password' => 'new123456', 'password_confirmation' => 'new123456'])->assertRedirect(route('login'));
    }

    public function test_non_tutor_cannot_access_tutor_profile(): void
    {
        $this->actingAs($this->adminUser);

        $this->get(route('tutor.profile'))->assertForbidden();
        $this->put(route('tutor.profile.update'), ['name' => 'Test', 'email' => 'test@prime.test'])->assertForbidden();
        $this->put(route('tutor.profile.password'), ['current_password' => 'password123', 'password' => 'new123456', 'password_confirmation' => 'new123456'])->assertForbidden();
    }

    public function test_tutor_can_view_own_profile(): void
    {
        $this->actingAs($this->tutor);

        $response = $this->get(route('tutor.profile'));

        $response->assertOk();
        $response->assertSee('Profil &amp; Pengaturan Akun', false);
        $response->assertSee('Budi Satria Tutor');
        $response->assertSee('budi.tutor@prime.test');
        $response->assertSee('Prime Academy Tutoring');
        $response->assertSee('12 SMA Matematika IPA');
        $response->assertSee('Cabang Suhat Malang');
    }

    public function test_tutor_can_update_profile_information(): void
    {
        $this->actingAs($this->tutor);

        $updateData = [
            'name' => 'Budi Satria Nugraha, S.Pd',
            'email' => 'budi.nugraha@prime.test',
        ];

        $response = $this->put(route('tutor.profile.update'), $updateData);

        $response->assertRedirect(route('tutor.profile'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $this->tutor->id,
            'name' => 'Budi Satria Nugraha, S.Pd',
            'email' => 'budi.nugraha@prime.test',
        ]);

        $this->tutor->refresh();
        $this->assertNull($this->tutor->email_verified_at);
    }

    public function test_tutor_profile_update_keeps_email_verification_if_email_unchanged(): void
    {
        $this->actingAs($this->tutor);

        $updateData = [
            'name' => 'Budi Satria Updated',
            'email' => $this->tutor->email,
        ];

        $response = $this->put(route('tutor.profile.update'), $updateData);

        $response->assertRedirect(route('tutor.profile'));
        $this->tutor->refresh();
        $this->assertNotNull($this->tutor->email_verified_at);
    }

    public function test_tutor_cannot_update_profile_with_existing_email(): void
    {
        $this->actingAs($this->tutor);

        $updateData = [
            'name' => 'Budi Satria',
            'email' => 'dewi.tutor@prime.test', // already used by otherTutor
        ];

        $response = $this->put(route('tutor.profile.update'), $updateData);

        $response->assertSessionHasErrors('email');
    }

    public function test_tutor_can_change_password(): void
    {
        $this->actingAs($this->tutor);

        $passwordData = [
            'current_password' => 'password123',
            'password' => 'newSecretPassword123',
            'password_confirmation' => 'newSecretPassword123',
        ];

        $response = $this->put(route('tutor.profile.password'), $passwordData);

        $response->assertRedirect(route('tutor.profile'));
        $response->assertSessionHas('success');

        $this->tutor->refresh();
        $this->assertTrue(Hash::check('newSecretPassword123', $this->tutor->password));
    }

    public function test_tutor_cannot_change_password_with_incorrect_current_password(): void
    {
        $this->actingAs($this->tutor);

        $passwordData = [
            'current_password' => 'wrongCurrentPassword',
            'password' => 'newSecretPassword123',
            'password_confirmation' => 'newSecretPassword123',
        ];

        $response = $this->put(route('tutor.profile.password'), $passwordData);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_tutor_cannot_change_password_with_unconfirmed_password(): void
    {
        $this->actingAs($this->tutor);

        $passwordData = [
            'current_password' => 'password123',
            'password' => 'newSecretPassword123',
            'password_confirmation' => 'mismatchedPassword123',
        ];

        $response = $this->put(route('tutor.profile.password'), $passwordData);

        $response->assertSessionHasErrors('password');
    }

    public function test_tutor_cannot_change_password_with_short_password(): void
    {
        $this->actingAs($this->tutor);

        $passwordData = [
            'current_password' => 'password123',
            'password' => 'short',
            'password_confirmation' => 'short',
        ];

        $response = $this->put(route('tutor.profile.password'), $passwordData);

        $response->assertSessionHasErrors('password');
    }

    public function test_tutor_can_logout(): void
    {
        $this->actingAs($this->tutor);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
