<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\HonorAssignment;
use App\Models\HonorScheme;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TutorAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerTutorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Branch $branchA1;

    protected Branch $branchA2;

    protected Branch $branchB;

    protected User $ownerA;

    protected User $tutorA1;

    protected User $tutorB;

    protected HonorScheme $schemeReguler;

    protected HonorScheme $schemeSenior;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Surabaya',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Competitor Bimbel',
            'slug' => 'competitor-bimbel',
            'status' => 'active',
        ]);

        $this->branchA1 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Gubeng',
            'code' => 'GBG',
            'city' => 'Surabaya',
            'status' => 'active',
        ]);

        $this->branchA2 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Rungkut',
            'code' => 'RKT',
            'city' => 'Surabaya',
            'status' => 'active',
        ]);

        $this->branchB = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Kuningan',
            'code' => 'KNG',
            'city' => 'Jakarta',
            'status' => 'active',
        ]);

        $this->ownerA = User::factory()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Pak Hendra Owner',
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->tutorA1 = User::factory()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Kak Dewi Lestari',
            'email' => 'tutor.dewi@cressco.test',
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->tutorB = User::factory()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Tutor Tenant B',
            'email' => 'tutor.b@cressco.test',
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $this->schemeReguler = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Skema Reguler Per Sesi',
            'method' => 'per_session',
            'rate' => 150000.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $this->ownerA->id,
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'honor_scheme_id' => $this->schemeReguler->id,
            'assignment_type' => 'default',
            'effective_from' => '2026-01-01',
        ]);

        $this->schemeSenior = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Skema Senior Per Sesi',
            'method' => 'per_session',
            'rate' => 200000.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $this->ownerA->id,
        ]);
    }

    public function test_owner_can_list_tutors(): void
    {
        $response = $this->actingAs($this->ownerA)->get('/owner/tutors');

        $response->assertOk();
        $response->assertSee('Management Tutor');
        $response->assertSee('Kak Dewi Lestari');
        $response->assertSee('Skema Reguler Per Sesi');
    }

    public function test_owner_cannot_see_other_tenant_tutors(): void
    {
        $response = $this->actingAs($this->ownerA)->get('/owner/tutors');

        $response->assertOk();
        $response->assertSee('Kak Dewi Lestari');
        $response->assertDontSee('Tutor Tenant B');
    }

    public function test_owner_can_create_tutor_with_override_scheme(): void
    {
        $payload = [
            'name' => 'Bambang Wijaya, S.Si.',
            'email' => 'tutor.bambang@cressco.test',
            'phone' => '081233445566',
            'status' => 'active',
            'honor_scheme_id' => $this->schemeSenior->id,
        ];

        $response = $this->actingAs($this->ownerA)->post('/owner/tutors', $payload);

        $tutor = User::where('email', 'tutor.bambang@cressco.test')->first();
        $this->assertNotNull($tutor);
        $this->assertEquals($this->tenantA->id, $tutor->tenant_id);
        $this->assertEquals('tutor', $tutor->role);

        $response->assertRedirect(route('owner.tutors.show', $tutor));

        // Verify override assignment created
        $this->assertDatabaseHas('honor_assignments', [
            'tenant_id' => $this->tenantA->id,
            'tutor_id' => $tutor->id,
            'honor_scheme_id' => $this->schemeSenior->id,
            'assignment_type' => 'tutor_override',
        ]);
    }

    public function test_owner_can_view_tutor_details_with_classes_and_sessions(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => '12 IPA - Matematika Intensif',
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'tutor_id' => $this->tutorA1->id,
            'class_id' => $class->id,
            'status' => 'active',
        ]);

        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'class_id' => $class->id,
            'scheduled_tutor_id' => $this->tutorA1->id,
            'actual_tutor_id' => $this->tutorA1->id,
            'session_date' => now()->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'status' => 'completed',
            'material' => 'Matriks dan Determinan',
        ]);

        $response = $this->actingAs($this->ownerA)->get("/owner/tutors/{$this->tutorA1->id}");

        $response->assertOk();
        $response->assertSee('Kak Dewi Lestari');
        $response->assertSee('12 IPA - Matematika Intensif');
        $response->assertSee('Matriks dan Determinan');
    }

    public function test_owner_cannot_view_other_tenant_tutor_details(): void
    {
        $response = $this->actingAs($this->ownerA)->get("/owner/tutors/{$this->tutorB->id}");

        $response->assertNotFound();
    }

    public function test_owner_can_update_tutor_and_honor_scheme_override(): void
    {
        $payload = [
            'name' => 'Dewi Lestari, M.Si.',
            'email' => 'tutor.dewi.updated@cressco.test',
            'phone' => '081299998888',
            'status' => 'active',
            'honor_scheme_id' => $this->schemeSenior->id,
        ];

        $response = $this->actingAs($this->ownerA)->put("/owner/tutors/{$this->tutorA1->id}", $payload);

        $response->assertRedirect();
        $this->tutorA1->refresh();
        $this->assertEquals('Dewi Lestari, M.Si.', $this->tutorA1->name);
        $this->assertEquals('tutor.dewi.updated@cressco.test', $this->tutorA1->email);

        $this->assertDatabaseHas('honor_assignments', [
            'tenant_id' => $this->tenantA->id,
            'tutor_id' => $this->tutorA1->id,
            'honor_scheme_id' => $this->schemeSenior->id,
            'assignment_type' => 'tutor_override',
        ]);
    }

    public function test_owner_can_toggle_tutor_status(): void
    {
        $response = $this->actingAs($this->ownerA)->patch("/owner/tutors/{$this->tutorA1->id}/toggle-status");

        $response->assertRedirect();
        $this->tutorA1->refresh();
        $this->assertEquals('inactive', $this->tutorA1->status);

        // Toggle back to active
        $this->actingAs($this->ownerA)->patch("/owner/tutors/{$this->tutorA1->id}/toggle-status");
        $this->tutorA1->refresh();
        $this->assertEquals('active', $this->tutorA1->status);
    }

    public function test_owner_can_assign_tutor_to_class(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => '10 SMA - Fisika Dasar',
            'status' => 'active',
        ]);

        $payload = [
            'class_id' => $class->id,
            'started_at' => '2026-02-01',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->ownerA)->post("/owner/tutors/{$this->tutorA1->id}/classes", $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('tutor_assignments', [
            'tenant_id' => $this->tenantA->id,
            'tutor_id' => $this->tutorA1->id,
            'class_id' => $class->id,
            'status' => 'active',
        ]);
    }

    public function test_owner_can_assign_and_clear_honor_scheme_override(): void
    {
        // Assign override
        $payload = [
            'honor_scheme_id' => $this->schemeSenior->id,
            'effective_from' => '2026-02-01',
        ];

        $res1 = $this->actingAs($this->ownerA)->post("/owner/tutors/{$this->tutorA1->id}/honor-scheme", $payload);
        $res1->assertRedirect();

        $this->assertDatabaseHas('honor_assignments', [
            'tenant_id' => $this->tenantA->id,
            'tutor_id' => $this->tutorA1->id,
            'honor_scheme_id' => $this->schemeSenior->id,
            'assignment_type' => 'tutor_override',
        ]);

        // Clear override (revert to default)
        $payloadClear = [
            'honor_scheme_id' => null,
            'effective_from' => '2026-02-01',
        ];

        $res2 = $this->actingAs($this->ownerA)->post("/owner/tutors/{$this->tutorA1->id}/honor-scheme", $payloadClear);
        $res2->assertRedirect();

        $this->assertDatabaseMissing('honor_assignments', [
            'tenant_id' => $this->tenantA->id,
            'tutor_id' => $this->tutorA1->id,
            'assignment_type' => 'tutor_override',
        ]);
    }

    public function test_tutor_search_and_status_filtering(): void
    {
        User::factory()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Sarah Wijaya',
            'email' => 'tutor.sarah@cressco.test',
            'role' => 'tutor',
            'status' => 'inactive',
        ]);

        // Search query
        $resSearch = $this->actingAs($this->ownerA)->get('/owner/tutors?search=Sarah');
        $resSearch->assertOk();
        $resSearch->assertSee('Sarah Wijaya');
        $resSearch->assertDontSee('Kak Dewi Lestari');

        // Status filter
        $resInactive = $this->actingAs($this->ownerA)->get('/owner/tutors?status=inactive');
        $resInactive->assertOk();
        $resInactive->assertSee('Sarah Wijaya');
        $resInactive->assertDontSee('Kak Dewi Lestari');
    }
}
