<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerPaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $owner;

    private Branch $branch;

    private Student $student;

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

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Surabaya',
            'code' => 'SBY',
            'city' => 'Surabaya',
            'status' => 'active',
        ]);

        $this->student = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Budi Santoso',
            'parent_name' => 'Santoso Senior',
            'parent_phone' => '081234567890',
            'status' => 'active',
        ]);
    }

    public function test_owner_can_view_payments_list(): void
    {
        Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $this->student->id,
            'period' => '2026-02',
            'amount' => 750000,
            'due_date' => now()->addDays(5),
            'status' => 'belum_bayar',
            'notes' => 'SPP Februari',
            'recorded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.payments.index'));

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('2026-02');
        $response->assertSee('750.000');
    }

    public function test_owner_can_filter_payments_by_status_and_branch(): void
    {
        $studentPaid = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Siswa Lunas',
            'status' => 'active',
        ]);

        $studentUnpaid = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Siswa Belum Bayar',
            'status' => 'active',
        ]);

        Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $studentPaid->id,
            'period' => '2026-02',
            'amount' => 500000,
            'due_date' => now()->subDays(1),
            'status' => 'lunas',
            'paid_at' => now(),
            'notes' => 'Invoice Lunas',
            'recorded_by' => $this->owner->id,
        ]);

        Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $studentUnpaid->id,
            'period' => '2026-03',
            'amount' => 650000,
            'due_date' => now()->addDays(10),
            'status' => 'belum_bayar',
            'notes' => 'Invoice Belum Bayar',
            'recorded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.payments.index', ['status' => 'lunas']));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('payments'));
        $this->assertEquals($studentPaid->id, $response->viewData('payments')->first()->student_id);
        $this->assertEquals('lunas', $response->viewData('payments')->first()->status);
        $this->assertEquals(500000, $response->viewData('payments')->first()->amount);
    }

    public function test_owner_can_create_payment_invoice(): void
    {
        $payload = [
            'branch_id' => $this->branch->id,
            'student_id' => $this->student->id,
            'period' => '2026-04',
            'amount' => 850000,
            'due_date' => now()->addDays(15)->format('Y-m-d'),
            'status' => 'belum_bayar',
            'notes' => 'Tagihan Les Intensif',
        ];

        $response = $this->actingAs($this->owner)
            ->post(route('owner.payments.store'), $payload);

        $response->assertRedirect(route('owner.payments.index'));
        $this->assertDatabaseHas('payments', [
            'tenant_id' => $this->tenant->id,
            'student_id' => $this->student->id,
            'period' => '2026-04',
            'amount' => 850000,
            'status' => 'belum_bayar',
        ]);
    }

    public function test_owner_can_view_payment_detail_with_reminder_generator(): void
    {
        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $this->student->id,
            'period' => '2026-02',
            'amount' => 500000,
            'due_date' => now()->addDays(5),
            'status' => 'belum_bayar',
            'recorded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.payments.show', $payment));

        $response->assertOk();
        $response->assertSee('Payment Reminder Generator');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Santoso Senior');
        $response->assertSee('500.000');
        $response->assertSee('Salin Pesan Reminder');
    }

    public function test_owner_can_verify_payment(): void
    {
        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $this->student->id,
            'period' => '2026-02',
            'amount' => 500000,
            'due_date' => now()->addDays(5),
            'status' => 'menunggu_verifikasi',
            'recorded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->patch(route('owner.payments.verify', $payment), [
                'notes' => 'Bukti transfer BCA valid',
            ]);

        $response->assertRedirect();
        $payment->refresh();

        $this->assertEquals('lunas', $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('Bukti transfer BCA valid', $payment->notes);
    }

    public function test_owner_can_update_payment(): void
    {
        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $this->student->id,
            'period' => '2026-02',
            'amount' => 500000,
            'due_date' => now()->addDays(5),
            'status' => 'belum_bayar',
            'recorded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->put(route('owner.payments.update', $payment), [
                'branch_id' => $this->branch->id,
                'student_id' => $this->student->id,
                'period' => '2026-02',
                'amount' => 600000,
                'due_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'lunas',
                'notes' => 'Nominal diupdate dan lunas',
            ]);

        $response->assertRedirect(route('owner.payments.show', $payment));
        $payment->refresh();

        $this->assertEquals(600000, $payment->amount);
        $this->assertEquals('lunas', $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_owner_can_delete_payment(): void
    {
        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'student_id' => $this->student->id,
            'period' => '2026-02',
            'amount' => 500000,
            'due_date' => now()->addDays(5),
            'status' => 'belum_bayar',
            'recorded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->delete(route('owner.payments.destroy', $payment));

        $response->assertRedirect(route('owner.payments.index'));
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }

    public function test_tenant_isolation_prevents_viewing_other_tenant_payment(): void
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
            'name' => 'Cabang Lain',
            'code' => 'LN',
            'city' => 'Jakarta',
            'status' => 'active',
        ]);

        $otherStudent = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $otherTenant->id,
            'branch_id' => $otherBranch->id,
            'name' => 'Siswa Lain',
            'status' => 'active',
        ]);

        $otherPayment = Payment::create([
            'tenant_id' => $otherTenant->id,
            'branch_id' => $otherBranch->id,
            'student_id' => $otherStudent->id,
            'period' => '2026-02',
            'amount' => 900000,
            'due_date' => now()->addDays(5),
            'status' => 'belum_bayar',
            'recorded_by' => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->get(route('owner.payments.show', $otherPayment));

        $response->assertForbidden();
    }
}
