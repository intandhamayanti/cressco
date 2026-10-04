<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\HonorAssignment;
use App\Models\HonorCalculation;
use App\Models\HonorScheme;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerHonorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Branch $branchA1;

    protected Branch $branchB;

    protected User $ownerA;

    protected User $tutorA1;

    protected User $tutorA2;

    protected User $tutorB;

    protected HonorScheme $schemeReguler;

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

        $this->tutorA2 = User::factory()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Bambang Wijaya',
            'email' => 'tutor.bambang@cressco.test',
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
    }

    public function test_owner_can_list_honors_and_schemes(): void
    {
        HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'tutor_id' => $this->tutorA1->id,
            'honor_scheme_id' => $this->schemeReguler->id,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'method' => 'per_session',
            'base_amount' => 1500000.00,
            'adjustment_amount' => 0.00,
            'final_amount' => 1500000.00,
            'status' => 'final',
            'calculated_by' => $this->ownerA->id,
        ]);

        $response = $this->actingAs($this->ownerA)->get('/owner/honors');

        $response->assertOk();
        $response->assertSee('Honor Tutor');
        $response->assertSee('Kak Dewi Lestari');
        $response->assertSee('Skema Reguler Per Sesi');
    }

    public function test_owner_cannot_see_other_tenant_honor_calculations(): void
    {
        $schemeB = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'name' => 'Skema Tenant B',
            'method' => 'per_session',
            'rate' => 200000.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $this->tutorB->id,
        ]);

        HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB->id,
            'tutor_id' => $this->tutorB->id,
            'honor_scheme_id' => $schemeB->id,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'method' => 'per_session',
            'base_amount' => 2000000.00,
            'adjustment_amount' => 0.00,
            'final_amount' => 2000000.00,
            'status' => 'paid',
            'calculated_by' => $this->tutorB->id,
        ]);

        $response = $this->actingAs($this->ownerA)->get('/owner/honors');

        $response->assertOk();
        $response->assertDontSee('Tutor Tenant B');
        $response->assertDontSee('Skema Tenant B');
    }

    public function test_owner_can_view_honor_calculation_detail_with_actual_sessions(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => '12 IPA - Fisika UTBK',
            'status' => 'active',
        ]);

        // Teaching session actual
        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'class_id' => $class->id,
            'scheduled_tutor_id' => $this->tutorA1->id,
            'actual_tutor_id' => $this->tutorA1->id,
            'session_date' => '2026-02-10',
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'status' => 'completed',
            'material' => 'Optika Geometri',
        ]);

        $calc = HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'tutor_id' => $this->tutorA1->id,
            'honor_scheme_id' => $this->schemeReguler->id,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'method' => 'per_session',
            'base_amount' => 150000.00,
            'adjustment_amount' => 50000.00,
            'final_amount' => 200000.00,
            'adjustment_reason' => 'Bonus Ketepatan Waktu',
            'status' => 'final',
            'calculated_by' => $this->ownerA->id,
        ]);

        $response = $this->actingAs($this->ownerA)->get("/owner/honors/{$calc->id}");

        $response->assertOk();
        $response->assertSee('Kak Dewi Lestari');
        $response->assertSee('Optika Geometri');
        $response->assertSee('Bonus Ketepatan Waktu');
    }

    public function test_honor_calculation_displays_actual_tutor_for_replacement_sessions(): void
    {
        $class = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => '11 IPA - Biologi',
            'status' => 'active',
        ]);

        // Sesi di mana tutor Dewi berhalangan dan digantikan oleh Bambang
        TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'class_id' => $class->id,
            'scheduled_tutor_id' => $this->tutorA1->id,
            'actual_tutor_id' => $this->tutorA2->id, // Actual tutor is Bambang
            'session_date' => '2026-02-15',
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'status' => 'completed',
            'material' => 'Genetika Populasi',
        ]);

        // Honor calculation for Bambang
        $calcBambang = HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'tutor_id' => $this->tutorA2->id,
            'honor_scheme_id' => $this->schemeReguler->id,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'method' => 'per_session',
            'base_amount' => 150000.00,
            'adjustment_amount' => 0.00,
            'final_amount' => 150000.00,
            'status' => 'final',
            'calculated_by' => $this->ownerA->id,
        ]);

        $response = $this->actingAs($this->ownerA)->get("/owner/honors/{$calcBambang->id}");

        $response->assertOk();
        $response->assertSee('Bambang Wijaya');
        $response->assertSee('Genetika Populasi');
        $response->assertSee('Menggantikan Tutor');
    }

    public function test_owner_can_mark_honor_as_paid(): void
    {
        $calc = HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'tutor_id' => $this->tutorA1->id,
            'honor_scheme_id' => $this->schemeReguler->id,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'method' => 'per_session',
            'base_amount' => 1500000.00,
            'adjustment_amount' => 0.00,
            'final_amount' => 1500000.00,
            'status' => 'final',
            'calculated_by' => $this->ownerA->id,
        ]);

        $response = $this->actingAs($this->ownerA)->patch("/owner/honors/{$calc->id}/mark-paid");

        $response->assertRedirect();
        $calc->refresh();
        $this->assertEquals('paid', $calc->status);
        $this->assertNotNull($calc->paid_at);
    }

    public function test_owner_can_create_honor_scheme_and_set_as_default(): void
    {
        $payload = [
            'name' => 'Skema Tetap Bulanan Full Time',
            'method' => 'fixed_monthly',
            'fixed_amount' => 4500000.00,
            'effective_from' => '2026-02-01',
            'status' => 'active',
            'is_default' => 1,
        ];

        $response = $this->actingAs($this->ownerA)->post('/owner/honors/schemes', $payload);

        $response->assertRedirect();
        $scheme = HonorScheme::where('name', 'Skema Tetap Bulanan Full Time')->first();
        $this->assertNotNull($scheme);
        $this->assertEquals('fixed_monthly', $scheme->method);

        // Verify default assignment updated
        $this->assertDatabaseHas('honor_assignments', [
            'tenant_id' => $this->tenantA->id,
            'honor_scheme_id' => $scheme->id,
            'assignment_type' => 'default',
        ]);
    }

    public function test_owner_can_update_scheme_and_toggle_status(): void
    {
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenantA->id,
            'name' => 'Skema Revenue Share 50%',
            'method' => 'revenue_share',
            'percentage' => 50.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $this->ownerA->id,
        ]);

        $updatePayload = [
            'name' => 'Skema Revenue Share 60%',
            'method' => 'revenue_share',
            'percentage' => 60.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
        ];

        $resUpdate = $this->actingAs($this->ownerA)->put("/owner/honors/schemes/{$scheme->id}", $updatePayload);
        $resUpdate->assertRedirect();
        $scheme->refresh();
        $this->assertEquals('Skema Revenue Share 60%', $scheme->name);
        $this->assertEquals(60.00, (float) $scheme->percentage);

        // Toggle status
        $resToggle = $this->actingAs($this->ownerA)->patch("/owner/honors/schemes/{$scheme->id}/toggle-status");
        $resToggle->assertRedirect();
        $scheme->refresh();
        $this->assertEquals('inactive', $scheme->status);
    }
}
