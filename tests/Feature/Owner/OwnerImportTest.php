<?php

namespace Tests\Feature\Owner;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerImportTest extends TestCase
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
            'name' => 'Prime Academy Malang',
            'slug' => 'prime-academy',
            'status' => 'active',
        ]);

        $this->owner = User::factory()->owner()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->branch = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Cabang Malang',
            'code' => 'MLG',
            'status' => 'active',
        ]);
    }

    public function test_owner_can_view_import_page_and_download_template(): void
    {
        $response = $this->actingAs($this->owner)
            ->get(route('owner.imports.create', ['type' => 'students']));

        $response->assertOk();
        $response->assertSee('Import Data Siswa');
        $response->assertSee('Download Template CSV');

        $templateResponse = $this->actingAs($this->owner)
            ->get(route('owner.imports.template', ['type' => 'students']));

        $templateResponse->assertOk();
        $templateResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_owner_can_preview_valid_student_csv_and_commit(): void
    {
        $csvContent = "Nama Lengkap,Jenis Kelamin,Tanggal Lahir,No Telepon,Alamat,Nama Orang Tua,No HP Orang Tua,Catatan\n";
        $csvContent .= "Doni Setiawan,L,2008-04-12,081234567890,Jl. Ijen No 10 Malang,Bapak Setiawan,081398765432,Target UTBK\n";

        $file = UploadedFile::fake()->createWithContent('students.csv', $csvContent);

        $previewResponse = $this->actingAs($this->owner)
            ->post(route('owner.imports.preview', ['type' => 'students']), [
                'file' => $file,
                'branch_id' => $this->branch->id,
            ]);

        $previewResponse->assertOk();
        $previewResponse->assertSee('Doni Setiawan');
        $previewResponse->assertSee('Laki-laki');
        $previewResponse->assertSee('Siap diimpor');

        $commitResponse = $this->actingAs($this->owner)
            ->post(route('owner.imports.commit', ['type' => 'students']), [
                'branch_id' => $this->branch->id,
                'rows' => [
                    [
                        'name' => 'Doni Setiawan',
                        'gender' => 'Laki-laki',
                        'date_of_birth' => '2008-04-12',
                        'phone' => '081234567890',
                        'address' => 'Jl. Ijen No 10 Malang',
                        'parent_name' => 'Bapak Setiawan',
                        'parent_phone' => '081398765432',
                        'notes' => 'Target UTBK',
                    ],
                ],
            ]);

        $commitResponse->assertRedirect(route('owner.students.index'));
        $this->assertDatabaseHas('students', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Doni Setiawan',
            'phone' => '081234567890',
        ]);
    }

    public function test_owner_can_preview_and_commit_tutors_csv(): void
    {
        $csvContent = "Nama Lengkap,Email,No Telepon,Spesialisasi\n";
        $csvContent .= "Dr. Hendra Gunawan,tutor.hendra@primeacademy.test,081299887766,Fisika Olimpiade\n";

        $file = UploadedFile::fake()->createWithContent('tutors.csv', $csvContent);

        $previewResponse = $this->actingAs($this->owner)
            ->post(route('owner.imports.preview', ['type' => 'tutors']), [
                'file' => $file,
                'branch_id' => $this->branch->id,
            ]);

        $previewResponse->assertOk();
        $previewResponse->assertSee('Dr. Hendra Gunawan');
        $previewResponse->assertSee('tutor.hendra@primeacademy.test');

        $commitResponse = $this->actingAs($this->owner)
            ->post(route('owner.imports.commit', ['type' => 'tutors']), [
                'branch_id' => $this->branch->id,
                'rows' => [
                    [
                        'name' => 'Dr. Hendra Gunawan',
                        'email' => 'tutor.hendra@primeacademy.test',
                        'phone' => '081299887766',
                        'specialty' => 'Fisika Olimpiade',
                    ],
                ],
            ]);

        $commitResponse->assertRedirect(route('owner.tutors.index'));
        $this->assertDatabaseHas('users', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Dr. Hendra Gunawan',
            'email' => 'tutor.hendra@primeacademy.test',
            'role' => 'tutor',
        ]);
    }
}
