<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\HonorCalculation;
use App\Models\HonorScheme;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerReportTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $owner;

    private Branch $branch1;

    private Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Surabaya',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->owner = User::factory()->owner()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->branch1 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Surabaya',
            'code' => 'SBY',
            'city' => 'Surabaya',
            'status' => 'active',
        ]);

        $this->branch2 = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Malang',
            'code' => 'MLG',
            'city' => 'Malang',
            'status' => 'active',
        ]);
    }

    public function test_owner_can_view_reports_dashboard(): void
    {
        $student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Budi Santoso',
            'status' => 'active',
        ]);

        Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'student_id' => $student->id,
            'period' => '2026-02',
            'amount' => 1000000,
            'due_date' => now()->addDays(5),
            'status' => 'lunas',
            'paid_at' => now(),
            'recorded_by' => $this->owner->id,
        ]);

        $tutor = User::factory()->tutor()->create(['tenant_id' => $this->tenant->id]);
        $scheme = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Skema Reguler',
            'method' => 'per_session',
            'rate' => 50000,
            'effective_from' => now()->startOfYear(),
            'is_default' => true,
            'status' => 'active',
            'created_by' => $this->owner->id,
        ]);

        HonorCalculation::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'tutor_id' => $tutor->id,
            'honor_scheme_id' => $scheme->id,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'method' => 'per_session',
            'base_amount' => 400000,
            'adjustment_amount' => 0,
            'final_amount' => 400000,
            'status' => 'final',
            'calculated_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.reports.index'));

        $response->assertOk();
        $response->assertSee('Laporan Keuangan & Kinerja');
        $response->assertSee('Cabang Surabaya');
        $response->assertSee('1.000.000'); // Revenue
        $response->assertSee('400.000');   // Expenses
    }

    public function test_owner_can_filter_reports_by_branch_and_period(): void
    {
        $student1 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'name' => 'Siswa 1',
            'status' => 'active',
        ]);

        $student2 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch2->id,
            'name' => 'Siswa 2',
            'status' => 'active',
        ]);

        Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'student_id' => $student1->id,
            'period' => '2026-02',
            'amount' => 1500000,
            'due_date' => now()->addDays(5),
            'status' => 'lunas',
            'paid_at' => now(),
            'recorded_by' => $this->owner->id,
        ]);

        Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch2->id,
            'student_id' => $student2->id,
            'period' => '2026-02',
            'amount' => 700000,
            'due_date' => now()->addDays(5),
            'status' => 'lunas',
            'paid_at' => now(),
            'recorded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.reports.index', ['branch_id' => $this->branch1->id, 'period' => '2026-02']));

        $response->assertOk();
        $response->assertSee('1.500.000');
    }

    public function test_tenant_isolation_in_reports(): void
    {
        $otherTenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Competitor Bimbel',
            'slug' => 'competitor-bimbel',
            'status' => 'active',
        ]);

        $otherBranch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'name' => 'Cabang Kompetitor',
            'code' => 'KMP',
            'city' => 'Jakarta',
            'status' => 'active',
        ]);

        $otherStudent = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'branch_id' => $otherBranch->id,
            'name' => 'Siswa Kompetitor',
            'status' => 'active',
        ]);

        Payment::create([
            'tenant_id' => $otherTenant->id,
            'branch_id' => $otherBranch->id,
            'student_id' => $otherStudent->id,
            'period' => '2026-02',
            'amount' => 9999999,
            'due_date' => now()->addDays(5),
            'status' => 'lunas',
            'paid_at' => now(),
            'recorded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.reports.index'));

        $response->assertOk();
        $response->assertDontSee('9.999.999');
    }
}
