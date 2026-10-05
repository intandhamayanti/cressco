<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\HonorAssignment;
use App\Models\HonorScheme;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OwnerImportService
{
    /**
     * Parse CSV file into rows with header keys.
     *
     * @return array<int, array<string, string>>
     */
    public function parseCsv(UploadedFile $file): array
    {
        $rows = [];
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return [];
        }

        $header = null;
        while (($row = fgetcsv($handle, 2000, ',')) !== false) {
            if (! $header) {
                // Sanitize UTF-8 BOM, whitespace, and normalize headers
                $header = array_map(function ($h) {
                    $clean = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strtolower($h)));

                    return str_replace([' ', '-'], '_', $clean);
                }, $row);

                continue;
            }

            if (empty(array_filter($row))) {
                continue; // skip blank line
            }

            $mapped = [];
            foreach ($header as $index => $colName) {
                $mapped[$colName] = isset($row[$index]) ? trim($row[$index]) : '';
            }

            $rows[] = $mapped;
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Validate and preview student import data.
     *
     * @param  array<int, array<string, string>>  $rawRows
     * @param  array<int, string>|null  $allowedBranchIds
     * @return array{valid: array<int, array<string, mixed>>, errors: array<int, array{row: int, name: string, reason: string}>, branches_found: array<string, string>}
     */
    public function previewStudents(Tenant $tenant, array $rawRows, ?array $allowedBranchIds = null): array
    {
        $valid = [];
        $errors = [];

        $branchQuery = Branch::where('tenant_id', $tenant->id);
        if ($allowedBranchIds !== null) {
            $branchQuery->whereIn('id', $allowedBranchIds);
        }
        $branches = $branchQuery->get()->keyBy(function ($b) {
            return strtolower(trim($b->name));
        });
        $branchCodes = $branches->keyBy(function ($b) {
            return strtolower(trim($b->code ?? ''));
        });

        // Load all active classes in tenant
        $classes = Classes::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->get();

        foreach ($rawRows as $idx => $row) {
            $rowNum = $idx + 2; // account for header (1-indexed)
            $name = $row['nama_siswa'] ?? $row['nama_lengkap'] ?? $row['nama'] ?? $row['name'] ?? '';
            $branchName = strtolower(trim($row['cabang'] ?? $row['branch'] ?? $row['kode_cabang'] ?? ''));
            $gender = $row['jenis_kelamin'] ?? $row['gender'] ?? 'Laki-laki';
            $phone = $row['no_telepon'] ?? $row['no_hp'] ?? $row['telepon'] ?? $row['phone'] ?? $row['no_wa'] ?? '';
            $parentName = $row['nama_orang_tua'] ?? $row['orang_tua'] ?? $row['parent_name'] ?? '';
            $parentPhone = $row['no_hp_orang_tua'] ?? $row['telepon_orang_tua'] ?? $row['no_wa_orang_tua'] ?? $row['parent_phone'] ?? '';
            $address = $row['alamat'] ?? $row['address'] ?? '';
            $classesRaw = $row['kelas'] ?? $row['classes'] ?? $row['pilihan_kelas'] ?? '';
            $notes = $row['catatan'] ?? $row['notes'] ?? '';

            if (empty($name)) {
                $errors[] = ['row' => $rowNum, 'name' => '-', 'reason' => 'Nama siswa wajib diisi.'];

                continue;
            }

            // Find branch
            $matchedBranch = $branches->get($branchName) ?? $branchCodes->get($branchName);
            if (! $matchedBranch) {
                // Default to first accessible branch if only 1 branch exists and branch not specified, otherwise error
                if ($branches->count() === 1 && empty($branchName)) {
                    $matchedBranch = $branches->first();
                } else {
                    $reason = empty($branchName)
                        ? 'Cabang wajib diisi.'
                        : "Cabang '{$branchName}' tidak ditemukan atau di luar hak akses Anda.";
                    $errors[] = ['row' => $rowNum, 'name' => $name, 'reason' => $reason];

                    continue;
                }
            }

            // Normalize gender
            $normalizedGender = (str_starts_with(strtolower($gender), 'p') || str_starts_with(strtolower($gender), 'w')) ? 'Perempuan' : 'Laki-laki';

            // Parse classes (supporting multi-class enrollment delimited by ; or ,)
            $matchedClassIds = [];
            $matchedClassNames = [];
            if (! empty($classesRaw)) {
                $classEntries = array_filter(array_map('trim', preg_split('/[;,]/', $classesRaw)));
                foreach ($classEntries as $entry) {
                    $lowEntry = strtolower($entry);
                    $foundClass = $classes->first(function ($c) use ($matchedBranch, $lowEntry) {
                        return $c->branch_id === $matchedBranch->id && (
                            strtolower($c->name) === $lowEntry ||
                            strtolower($c->subject ?? '') === $lowEntry ||
                            str_contains(strtolower($c->name), $lowEntry)
                        );
                    });

                    if ($foundClass && ! in_array($foundClass->id, $matchedClassIds, true)) {
                        $matchedClassIds[] = $foundClass->id;
                        $matchedClassNames[] = $foundClass->name;
                    }
                }
            }

            $valid[] = [
                'name' => $name,
                'branch_id' => $matchedBranch->id,
                'branch_name' => $matchedBranch->name,
                'gender' => $normalizedGender,
                'phone' => $phone,
                'parent_name' => $parentName,
                'parent_phone' => $parentPhone,
                'address' => $address,
                'class_ids' => $matchedClassIds,
                'class_names' => $matchedClassNames,
                'notes' => $notes,
            ];
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
            'branches_found' => $branches->pluck('name', 'id')->all(),
        ];
    }

    /**
     * Commit valid students into database with optional multi-class enrollments.
     *
     * @param  array<int, array<string, mixed>>  $validRows
     * @return int Count of students imported
     */
    public function commitStudents(Tenant $tenant, array $validRows): int
    {
        $count = 0;
        foreach ($validRows as $item) {
            $student = Student::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'branch_id' => $item['branch_id'],
                'name' => $item['name'],
                'gender' => $item['gender'] ?? 'Laki-laki',
                'phone' => $item['phone'] ?? null,
                'parent_name' => $item['parent_name'] ?? null,
                'parent_phone' => $item['parent_phone'] ?? null,
                'address' => $item['address'] ?? null,
                'notes' => $item['notes'] ?? null,
                'joined_at' => now()->toDateString(),
                'status' => 'active',
            ]);

            // Enroll to multiple classes if provided
            if (! empty($item['class_ids']) && is_array($item['class_ids'])) {
                foreach ($item['class_ids'] as $classId) {
                    Enrollment::firstOrCreate([
                        'tenant_id' => $tenant->id,
                        'branch_id' => $student->branch_id,
                        'student_id' => $student->id,
                        'class_id' => $classId,
                    ], [
                        'id' => (string) Str::uuid(),
                        'started_at' => $student->joined_at ?? now()->toDateString(),
                        'status' => 'active',
                    ]);
                }
            }

            $count++;
        }

        return $count;
    }

    /**
     * Validate and preview tutor import data.
     *
     * @param  array<int, array<string, string>>  $rawRows
     * @return array{valid: array<int, array<string, mixed>>, errors: array<int, array{row: int, name: string, reason: string}>, schemes_found: array<string, string>}
     */
    public function previewTutors(Tenant $tenant, array $rawRows): array
    {
        $valid = [];
        $errors = [];

        $existingEmails = User::pluck('email')->map(fn ($e) => strtolower($e))->all();
        $schemes = HonorScheme::where('tenant_id', $tenant->id)->get()->keyBy(function ($s) {
            return strtolower($s->name);
        });

        foreach ($rawRows as $idx => $row) {
            $rowNum = $idx + 2;
            $name = $row['nama_tutor'] ?? $row['nama_lengkap'] ?? $row['nama'] ?? $row['name'] ?? '';
            $email = strtolower($row['email'] ?? '');
            $phone = $row['no_telepon'] ?? $row['no_hp'] ?? $row['telepon'] ?? $row['phone'] ?? $row['no_wa'] ?? '';
            $schemeName = strtolower($row['skema_honor'] ?? $row['honor_scheme'] ?? '');

            if (empty($name)) {
                $errors[] = ['row' => $rowNum, 'name' => '-', 'reason' => 'Nama tutor wajib diisi.'];

                continue;
            }

            if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = ['row' => $rowNum, 'name' => $name, 'reason' => "Format email '{$email}' tidak valid."];

                continue;
            }

            if (in_array($email, $existingEmails, true)) {
                $errors[] = ['row' => $rowNum, 'name' => $name, 'reason' => "Email '{$email}' sudah terdaftar di sistem."];

                continue;
            }

            $matchedScheme = null;
            if (! empty($schemeName)) {
                $matchedScheme = $schemes->get($schemeName);
            }

            $valid[] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'honor_scheme_id' => $matchedScheme?->id,
                'honor_scheme_name' => $matchedScheme?->name ?? 'Skema Default',
            ];

            $existingEmails[] = $email; // prevent duplicate emails within same file
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
            'schemes_found' => $schemes->pluck('name', 'id')->all(),
        ];
    }

    /**
     * Commit valid tutors into database.
     *
     * @param  array<int, array<string, mixed>>  $validRows
     * @return int Count of tutors imported
     */
    public function commitTutors(Tenant $tenant, array $validRows): int
    {
        $count = 0;
        foreach ($validRows as $item) {
            $tutor = User::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'name' => $item['name'],
                'email' => $item['email'],
                'phone' => $item['phone'] ?? null,
                'role' => 'tutor',
                'status' => 'active',
                'password' => Hash::make(Str::password(16)),
                'email_verified_at' => now(),
            ]);

            if (! empty($item['honor_scheme_id'])) {
                HonorAssignment::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenant->id,
                    'tutor_id' => $tutor->id,
                    'honor_scheme_id' => $item['honor_scheme_id'],
                    'assignment_type' => 'tutor_override',
                    'effective_from' => now()->startOfMonth()->toDateString(),
                ]);
            }

            $count++;
        }

        return $count;
    }

    /**
     * Generate sample CSV template string for students or tutors.
     */
    public function getTemplateCsv(string $type, Tenant $tenant): string
    {
        if ($type === 'students') {
            $firstBranch = Branch::where('tenant_id', $tenant->id)->first()?->name ?? 'Cabang Pusat';
            $classes = Classes::where('tenant_id', $tenant->id)->where('status', 'active')->take(2)->pluck('name')->all();
            $exampleClass1 = $classes[0] ?? '10 SMA - Matematika';
            $exampleClass2 = isset($classes[1]) ? "{$classes[0]}; {$classes[1]}" : "{$exampleClass1}; 10 SMA - Fisika";

            return "nama_siswa,cabang,jenis_kelamin,telepon,nama_orang_tua,telepon_orang_tua,alamat,kelas,catatan\n".
                   "Budi Santoso,{$firstBranch},Laki-laki,08123456789,Santoso,08139876543,Jl. Merdeka No. 10,\"{$exampleClass2}\",Target lolos PTN\n".
                   "Siti Nurhaliza,{$firstBranch},Perempuan,08123456780,Nurhadi,08139876540,Jl. Sudirman No. 25,\"{$exampleClass1}\",Peminatan Bahasa Inggris\n";
        }

        $firstScheme = HonorScheme::where('tenant_id', $tenant->id)->first()?->name ?? 'Skema Honor Reguler Per Sesi';

        return "nama_tutor,email,telepon,skema_honor\n".
               "Ahmad Fauzi, S.Pd.,ahmad.fauzi@example.com,08123456781,{$firstScheme}\n".
               "Dewi Lestari, M.Si.,dewi.lestari@example.com,08123456782,{$firstScheme}\n";
    }
}
