<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentResult;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\BranchUser;
use App\Models\Classes;
use App\Models\Enrollment;
use App\Models\HonorAssignment;
use App\Models\HonorCalculation;
use App\Models\HonorScheme;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeachingSession;
use App\Models\Tenant;
use App\Models\TenantSetting;
use App\Models\TutorAssignment;
use App\Models\TutorAttendance;
use App\Models\TutorReplacement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the realistic demo dataset seeder.
     */
    public function run(): void
    {
        // -------------------------------------------------------------
        // 1. Super Admin (Platform-scoped, tenant_id = null)
        // -------------------------------------------------------------
        $superAdmin = User::where('email', 'superadmin@cressco.test')->first();
        if (! $superAdmin) {
            $superAdmin = User::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => null,
                'name' => 'Super Admin Cressco',
                'email' => 'superadmin@cressco.test',
                'email_verified_at' => now(),
                'password' => Hash::make('Password123!'),
                'role' => 'super_admin',
                'status' => 'active',
            ]);
        } else {
            $superAdmin->update([
                'name' => 'Super Admin Cressco',
                'password' => Hash::make('Password123!'),
                'role' => 'super_admin',
                'status' => 'active',
            ]);
        }

        // -------------------------------------------------------------
        // 2. Demo Tenant: Prime Academy
        // -------------------------------------------------------------
        $tenant = Tenant::where('slug', 'prime-academy')->first();
        if (! $tenant) {
            $tenant = Tenant::create([
                'id' => (string) Str::uuid(),
                'name' => 'Prime Academy Tutoring Center',
                'slug' => 'prime-academy',
                'status' => 'active',
                'logo' => null,
                'description' => 'Bimbel Kursus Modern: Programming, English & Design',
                'address' => 'Jl. Ijen No. 12, Malang, Jawa Timur',
                'phone' => '0341-551234',
                'email' => 'admin@primeacademy.id',
            ]);
        } else {
            $tenant->update([
                'name' => 'Prime Academy Tutoring Center',
                'status' => 'active',
                'description' => 'Bimbel Kursus Modern: Programming, English & Design',
                'address' => 'Jl. Ijen No. 12, Malang, Jawa Timur',
                'phone' => '0341-551234',
                'email' => 'admin@primeacademy.id',
            ]);
        }

        $tenantId = $tenant->id;

        // Clean existing demo data under this tenant to ensure 100% idempotency
        $this->cleanExistingTenantData($tenantId);

        // Tenant Settings
        TenantSetting::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'key' => 'payment_due_day',
            'value' => ['day' => 10],
        ]);

        TenantSetting::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'key' => 'timezone',
            'value' => ['timezone' => 'Asia/Jakarta'],
        ]);

        TenantSetting::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'key' => 'currency',
            'value' => ['code' => 'IDR', 'symbol' => 'Rp'],
        ]);

        // -------------------------------------------------------------
        // 3. Branches (3 Active Branches)
        // -------------------------------------------------------------
        $branchMalang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Prime Academy - Malang',
            'code' => 'MLG-01',
            'address' => 'Jl. Ijen No. 12, Klojen, Kota Malang, Jawa Timur',
            'phone' => '0341-551234',
            'status' => 'active',
        ]);

        $branchMakassar = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Prime Academy - Makassar',
            'code' => 'MKS-01',
            'address' => 'Jl. AP Pettarani No. 88, Panakkukang, Makassar, Sulawesi Selatan',
            'phone' => '0411-884567',
            'status' => 'active',
        ]);

        $branchSemarang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Prime Academy - Semarang',
            'code' => 'SMG-01',
            'address' => 'Jl. Pandanaran No. 45, Mugassari, Semarang Selatan, Jawa Tengah',
            'phone' => '024-8419876',
            'status' => 'active',
        ]);

        $branches = [
            'malang' => $branchMalang,
            'makassar' => $branchMakassar,
            'semarang' => $branchSemarang,
        ];

        // -------------------------------------------------------------
        // 4. Users: 1 Owner, 4 Admins, 10 Tutors
        // -------------------------------------------------------------
        $owner = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Budi Pratama, M.Ed.',
            'email' => 'owner@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        // Admins
        $adminMalang = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Siti Rahmawati',
            'email' => 'admin.malang@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $adminMakassar = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Rizky Ramadhan',
            'email' => 'admin.makassar@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $adminSemarang = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Nurul Aini',
            'email' => 'admin.semarang@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $adminPusat = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Hendra Saputra',
            'email' => 'admin.pusat@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // BranchUser associations
        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'branch_id' => $branchMalang->id,
            'user_id' => $adminMalang->id,
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'branch_id' => $branchMakassar->id,
            'user_id' => $adminMakassar->id,
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'branch_id' => $branchSemarang->id,
            'user_id' => $adminSemarang->id,
        ]);

        foreach ([$branchMalang, $branchMakassar, $branchSemarang] as $branch) {
            BranchUser::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'branch_id' => $branch->id,
                'user_id' => $adminPusat->id,
            ]);
        }

        // 10 Tutors tailored for Programming, English, Design
        $tutorData = [
            [
                'key' => 'rian',
                'name' => 'Rian Hidayat, M.Kom.',
                'email' => 'tutor.rian@cressco.test',
                'specialty' => 'Programming Python & Web Backend',
            ],
            [
                'key' => 'kevin',
                'name' => 'Kevin Sanjaya, S.Kom.',
                'email' => 'tutor.kevin@cressco.test',
                'specialty' => 'Game Development & Unity 3D',
            ],
            [
                'key' => 'arif',
                'name' => 'Arif Rahman, S.Kom.',
                'email' => 'tutor.arif@cressco.test',
                'specialty' => 'Web Frontend & React JS',
            ],
            [
                'key' => 'maya',
                'name' => 'Maya Safitri, M.Pd.',
                'email' => 'tutor.maya@cressco.test',
                'specialty' => 'English Conversation & Business English',
            ],
            [
                'key' => 'putri',
                'name' => 'Putri Utami, M.Ed.',
                'email' => 'tutor.putri@cressco.test',
                'specialty' => 'IELTS Academic & Speaking Specialist',
            ],
            [
                'key' => 'sarah',
                'name' => 'Sarah Wijaya, S.Hum.',
                'email' => 'tutor.sarah@cressco.test',
                'specialty' => 'TOEFL Preparation & English Grammar',
            ],
            [
                'key' => 'anisa',
                'name' => 'Anisa Rahmawati, S.Pd.',
                'email' => 'tutor.anisa@cressco.test',
                'specialty' => 'English Speaking & Public Speaking',
            ],
            [
                'key' => 'dimas',
                'name' => 'Dimas Pratama, S.Sn.',
                'email' => 'tutor.dimas@cressco.test',
                'specialty' => 'UI/UX Design & Prototyping Figma',
            ],
            [
                'key' => 'dewi',
                'name' => 'Dewi Lestari, M.Ds.',
                'email' => 'tutor.dewi@cressco.test',
                'specialty' => 'Graphic Design & Visual Branding',
            ],
            [
                'key' => 'bambang',
                'name' => 'Bambang Wijaya, S.Sn.',
                'email' => 'tutor.bambang@cressco.test',
                'specialty' => 'Digital Illustration & Concept Art',
            ],
        ];

        $tutors = [];
        foreach ($tutorData as $t) {
            $tutors[$t['key']] = User::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'name' => $t['name'],
                'email' => $t['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('Password123!'),
                'role' => 'tutor',
                'status' => 'active',
            ]);
        }

        // -------------------------------------------------------------
        // 5. Honor Schemes & Assignments
        // -------------------------------------------------------------
        $schemeReguler = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Skema Honor Reguler Per Sesi',
            'method' => 'per_session',
            'rate' => 150000.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        $schemeSenior = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Skema Honor Senior Tutor',
            'method' => 'per_session',
            'rate' => 200000.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        $schemeSpecialist = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Skema Honor Spesialis Game & IELTS',
            'method' => 'per_session',
            'rate' => 250000.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        $schemeKoordinator = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'name' => 'Skema Honor Tutor Tetap & Koordinator',
            'method' => 'fixed_monthly',
            'fixed_amount' => 3000000.00,
            'rate' => 100000.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        // Default tenant honor assignment
        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'tutor_id' => null,
            'honor_scheme_id' => $schemeReguler->id,
            'assignment_type' => 'default',
            'effective_from' => '2026-01-01',
        ]);

        // Tutor overrides
        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'tutor_id' => $tutors['dewi']->id,
            'honor_scheme_id' => $schemeSenior->id,
            'assignment_type' => 'tutor_override',
            'effective_from' => '2026-01-01',
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'tutor_id' => $tutors['putri']->id,
            'honor_scheme_id' => $schemeSpecialist->id,
            'assignment_type' => 'tutor_override',
            'effective_from' => '2026-01-01',
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'tutor_id' => $tutors['kevin']->id,
            'honor_scheme_id' => $schemeSpecialist->id,
            'assignment_type' => 'tutor_override',
            'effective_from' => '2026-01-01',
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'tutor_id' => $tutors['rian']->id,
            'honor_scheme_id' => $schemeKoordinator->id,
            'assignment_type' => 'tutor_override',
            'effective_from' => '2026-01-01',
        ]);

        // -------------------------------------------------------------
        // 6. Classes (12 Classes across 3 Core Programs)
        // -------------------------------------------------------------
        $classesConfig = [
            // Malang Classes (4 classes)
            [
                'key' => 'mlg_prog_a',
                'branch' => 'malang',
                'name' => 'Programming Dasar A',
                'subject' => 'Programming',
                'level' => 'Pemula',
                'capacity' => 15,
                'monthly_fee' => 650000.00,
                'primary_tutor' => 'rian',
                'secondary_tutors' => ['arif'],
                'schedules' => [
                    ['day' => 1, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Lab Komputer M-1'],
                    ['day' => 4, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Lab Komputer M-1'],
                ],
            ],
            [
                'key' => 'mlg_web_dev',
                'branch' => 'malang',
                'name' => 'Web Development',
                'subject' => 'Programming',
                'level' => 'Menengah',
                'capacity' => 15,
                'monthly_fee' => 750000.00,
                'primary_tutor' => 'arif',
                'secondary_tutors' => ['rian'],
                'schedules' => [
                    ['day' => 3, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Lab Komputer M-2'],
                    ['day' => 6, 'start' => '09:00:00', 'end' => '10:30:00', 'room' => 'Lab Komputer M-2'],
                ],
            ],
            [
                'key' => 'mlg_eng_spk',
                'branch' => 'malang',
                'name' => 'English Speaking',
                'subject' => 'English',
                'level' => 'Pemula',
                'capacity' => 16,
                'monthly_fee' => 550000.00,
                'primary_tutor' => 'anisa',
                'secondary_tutors' => ['maya'],
                'schedules' => [
                    ['day' => 2, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Ruang E-1'],
                ],
            ],
            [
                'key' => 'mlg_uiux_dsr',
                'branch' => 'malang',
                'name' => 'UI/UX Design Dasar',
                'subject' => 'Design',
                'level' => 'Pemula',
                'capacity' => 14,
                'monthly_fee' => 700000.00,
                'primary_tutor' => 'dimas',
                'secondary_tutors' => ['dewi'],
                'schedules' => [
                    ['day' => 5, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Design Studio M'],
                ],
            ],

            // Makassar Classes (4 classes)
            [
                'key' => 'mks_prog_b',
                'branch' => 'makassar',
                'name' => 'Programming Dasar B',
                'subject' => 'Programming',
                'level' => 'Pemula',
                'capacity' => 15,
                'monthly_fee' => 650000.00,
                'primary_tutor' => 'rian',
                'secondary_tutors' => ['kevin'],
                'schedules' => [
                    ['day' => 2, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Lab Komputer P-1'],
                    ['day' => 5, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Lab Komputer P-1'],
                ],
            ],
            [
                'key' => 'mks_game_dev',
                'branch' => 'makassar',
                'name' => 'Game Development',
                'subject' => 'Programming',
                'level' => 'Menengah',
                'capacity' => 12,
                'monthly_fee' => 850000.00,
                'primary_tutor' => 'kevin',
                'secondary_tutors' => ['arif'],
                'schedules' => [
                    ['day' => 1, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Lab Game Studio'],
                    ['day' => 4, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Lab Game Studio'],
                ],
            ],
            [
                'key' => 'mks_eng_conv',
                'branch' => 'makassar',
                'name' => 'English Conversation',
                'subject' => 'English',
                'level' => 'Menengah',
                'capacity' => 16,
                'monthly_fee' => 550000.00,
                'primary_tutor' => 'maya',
                'secondary_tutors' => ['putri'],
                'schedules' => [
                    ['day' => 3, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Ruang E-2'],
                ],
            ],
            [
                'key' => 'mks_graph_dsn',
                'branch' => 'makassar',
                'name' => 'Graphic Design',
                'subject' => 'Design',
                'level' => 'Menengah',
                'capacity' => 14,
                'monthly_fee' => 700000.00,
                'primary_tutor' => 'dewi',
                'secondary_tutors' => ['bambang'],
                'schedules' => [
                    ['day' => 6, 'start' => '10:00:00', 'end' => '11:30:00', 'room' => 'Studio Desain P'],
                ],
            ],

            // Semarang Classes (4 classes)
            [
                'key' => 'smg_web_adv',
                'branch' => 'semarang',
                'name' => 'Web Development',
                'subject' => 'Programming',
                'level' => 'Lanjutan',
                'capacity' => 15,
                'monthly_fee' => 800000.00,
                'primary_tutor' => 'arif',
                'secondary_tutors' => ['rian'],
                'schedules' => [
                    ['day' => 1, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Lab Komputer S-1'],
                    ['day' => 4, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Lab Komputer S-1'],
                ],
            ],
            [
                'key' => 'smg_ielts_prep',
                'branch' => 'semarang',
                'name' => 'IELTS Preparation',
                'subject' => 'English',
                'level' => 'Lanjutan',
                'capacity' => 12,
                'monthly_fee' => 950000.00,
                'primary_tutor' => 'putri',
                'secondary_tutors' => ['sarah'],
                'schedules' => [
                    ['day' => 3, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'IELTS Lab S'],
                    ['day' => 6, 'start' => '09:00:00', 'end' => '10:30:00', 'room' => 'IELTS Lab S'],
                ],
            ],
            [
                'key' => 'smg_toefl_prep',
                'branch' => 'semarang',
                'name' => 'TOEFL Preparation',
                'subject' => 'English',
                'level' => 'Lanjutan',
                'capacity' => 14,
                'monthly_fee' => 900000.00,
                'primary_tutor' => 'sarah',
                'secondary_tutors' => ['maya'],
                'schedules' => [
                    ['day' => 2, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Ruang E-3'],
                ],
            ],
            [
                'key' => 'smg_dig_illus',
                'branch' => 'semarang',
                'name' => 'Digital Illustration',
                'subject' => 'Design',
                'level' => 'Menengah',
                'capacity' => 12,
                'monthly_fee' => 750000.00,
                'primary_tutor' => 'bambang',
                'secondary_tutors' => ['dimas'],
                'schedules' => [
                    ['day' => 5, 'start' => '16:00:00', 'end' => '17:30:00', 'room' => 'Art Studio S'],
                ],
            ],
        ];

        $classes = [];
        $classFeeMap = [];
        $allSchedules = [];

        foreach ($classesConfig as $cfg) {
            $branchObj = $branches[$cfg['branch']];
            $class = Classes::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'branch_id' => $branchObj->id,
                'name' => $cfg['name'],
                'subject' => $cfg['subject'],
                'level' => $cfg['level'],
                'capacity' => $cfg['capacity'],
                'status' => 'active',
            ]);

            $classes[$cfg['key']] = $class;
            $classFeeMap[$class->id] = $cfg['monthly_fee'];

            // Tutor Assignments
            $primaryTutor = $tutors[$cfg['primary_tutor']];
            TutorAssignment::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'branch_id' => $branchObj->id,
                'tutor_id' => $primaryTutor->id,
                'class_id' => $class->id,
                'started_at' => '2026-01-05',
                'status' => 'active',
            ]);

            foreach ($cfg['secondary_tutors'] as $secKey) {
                if (isset($tutors[$secKey])) {
                    TutorAssignment::create([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchObj->id,
                        'tutor_id' => $tutors[$secKey]->id,
                        'class_id' => $class->id,
                        'started_at' => '2026-01-05',
                        'status' => 'active',
                    ]);
                }
            }

            // Recurring Schedules
            foreach ($cfg['schedules'] as $sch) {
                $schedule = Schedule::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchObj->id,
                    'class_id' => $class->id,
                    'scheduled_tutor_id' => $primaryTutor->id,
                    'day_of_week' => $sch['day'],
                    'start_time' => $sch['start'],
                    'end_time' => $sch['end'],
                    'room' => $sch['room'],
                    'starts_on' => '2026-01-05',
                    'status' => 'active',
                ]);

                $allSchedules[] = [
                    'schedule' => $schedule,
                    'class' => $class,
                    'branch' => $branchObj,
                    'primary_tutor' => $primaryTutor,
                    'secondary_tutor_ids' => array_map(fn ($k) => $tutors[$k]->id, $cfg['secondary_tutors']),
                    'day_of_week' => $sch['day'],
                    'start_time' => $sch['start'],
                    'end_time' => $sch['end'],
                    'room' => $sch['room'],
                    'subject' => $cfg['subject'],
                ];
            }
        }

        // -------------------------------------------------------------
        // 7. Students (84 Students across 3 Branches)
        // -------------------------------------------------------------
        $studentNames = [
            'malang' => [
                ['name' => 'Andi Pratama', 'gender' => 'Laki-laki', 'dob' => '2007-05-14', 'parent' => 'Joko Pratama', 'addr' => 'Jl. Ijen No. 45, Malang', 'note' => 'Minat Web Fullstack'],
                ['name' => 'Nadia Amanda', 'gender' => 'Perempuan', 'dob' => '2007-08-20', 'parent' => 'Hendra Amanda', 'addr' => 'Jl. Soekarno Hatta No. 22, Malang', 'note' => 'Portofolio UI/UX'],
                ['name' => 'Bayu Saputra', 'gender' => 'Laki-laki', 'dob' => '2008-03-11', 'parent' => 'Bambang Saputra', 'addr' => 'Jl. Bandung No. 15, Malang', 'note' => 'Dasar Python & Logic'],
                ['name' => 'Clarissa Aurelia', 'gender' => 'Perempuan', 'dob' => '2008-11-28', 'parent' => 'Surya Aurelius', 'addr' => 'Jl. MT Haryono No. 88, Malang', 'note' => 'English Speaking & IELTS'],
                ['name' => 'Daffa Alfarizi', 'gender' => 'Laki-laki', 'dob' => '2007-01-19', 'parent' => 'Farid Alfarizi', 'addr' => 'Jl. Borobudur No. 10, Malang', 'note' => 'Frontend React Developer'],
                ['name' => 'Zahra Kirana', 'gender' => 'Perempuan', 'dob' => '2008-07-04', 'parent' => 'Kiran Hadi', 'addr' => 'Jl. Veteran No. 5, Malang', 'note' => 'Product Design Figma'],
                ['name' => 'Rafi Pratama', 'gender' => 'Laki-laki', 'dob' => '2009-04-12', 'parent' => 'Danang Pratama', 'addr' => 'Jl. Sigura-gura No. 3, Malang', 'note' => 'Coding Python'],
                ['name' => 'Gisella Pramesti', 'gender' => 'Perempuan', 'dob' => '2008-09-17', 'parent' => 'Pramono Hadi', 'addr' => 'Jl. Jakarta No. 12, Malang', 'note' => 'English Public Speaking'],
                ['name' => 'Arka Raditya', 'gender' => 'Laki-laki', 'dob' => '2007-02-23', 'parent' => 'Radit Gunawan', 'addr' => 'Jl. Bogor No. 8, Malang', 'note' => 'Web Backend Architecture'],
                ['name' => 'Keisha Salsabila', 'gender' => 'Perempuan', 'dob' => '2008-06-30', 'parent' => 'Salsabil Ahmad', 'addr' => 'Jl. Danau Toba No. 14, Malang', 'note' => 'UI/UX Mobile App'],
                ['name' => 'Farhan Maulana', 'gender' => 'Laki-laki', 'dob' => '2009-10-15', 'parent' => 'Maulana Malik', 'addr' => 'Jl. Kawi No. 27, Malang', 'note' => 'Programming Dasar'],
                ['name' => 'Tari Puspitasari', 'gender' => 'Perempuan', 'dob' => '2008-12-08', 'parent' => 'Puspito Rahardjo', 'addr' => 'Jl. Semeru No. 9, Malang', 'note' => 'English Communication'],
                ['name' => 'Gilang Ramadhan', 'gender' => 'Laki-laki', 'dob' => '2007-05-18', 'parent' => 'Ramadhani Syarif', 'addr' => 'Jl. Galunggung No. 41, Malang', 'note' => 'Fullstack Web Project'],
                ['name' => 'Salwa Nafisa', 'gender' => 'Perempuan', 'dob' => '2008-08-25', 'parent' => 'Nafis Abdullah', 'addr' => 'Jl. Dieng No. 19, Malang', 'note' => 'User Research & Prototyping'],
                ['name' => 'Rendra Wicaksono', 'gender' => 'Laki-laki', 'dob' => '2007-06-09', 'parent' => 'Wicaksono Tri', 'addr' => 'Jl. Candi Mendut No. 6, Malang', 'note' => 'Web Development'],
                ['name' => 'Aurelia Jasmine', 'gender' => 'Perempuan', 'dob' => '2008-09-14', 'parent' => 'Jasmine Yusuf', 'addr' => 'Jl. Pahlawan Trip No. 2, Malang', 'note' => 'English Presentation'],
                ['name' => 'Dimas Aditya', 'gender' => 'Laki-laki', 'dob' => '2007-12-01', 'parent' => 'Aditya Hermawan', 'addr' => 'Jl. Sulfat No. 55, Malang', 'note' => 'Javascript & Python'],
                ['name' => 'Nabila Syakirah', 'gender' => 'Perempuan', 'dob' => '2008-04-18', 'parent' => 'Syakir Anwar', 'addr' => 'Jl. Buring No. 11, Malang', 'note' => 'UI Designer'],
                ['name' => 'Irfan Hakim', 'gender' => 'Laki-laki', 'dob' => '2009-07-21', 'parent' => 'Hakim Basuki', 'addr' => 'Jl. Surabaya No. 8, Malang', 'note' => 'Logic & Algoritma'],
                ['name' => 'Meisya Putri', 'gender' => 'Perempuan', 'dob' => '2008-10-05', 'parent' => 'Putra Sanjaya', 'addr' => 'Jl. Wilis No. 4, Malang', 'note' => 'Design & UI/UX'],
                ['name' => 'Reza Pahlevi', 'gender' => 'Laki-laki', 'dob' => '2007-03-14', 'parent' => 'Pahlevi Akbar', 'addr' => 'Jl. Ciliwung No. 32, Malang', 'note' => 'Web Frontend'],
                ['name' => 'Syifa Fauziyah', 'gender' => 'Perempuan', 'dob' => '2008-08-19', 'parent' => 'Fauzi Ridwan', 'addr' => 'Jl. Sunan Kalijaga No. 7, Malang', 'note' => 'English Speaking Club'],
                ['name' => 'Bintang Pratama', 'gender' => 'Laki-laki', 'dob' => '2009-01-27', 'parent' => 'Pratama Yudi', 'addr' => 'Jl. Tlogomas No. 89, Malang', 'note' => 'Python Basics'],
                ['name' => 'Kayla Az-Zahra', 'gender' => 'Perempuan', 'dob' => '2008-04-11', 'parent' => 'Zahrul Munir', 'addr' => 'Jl. Joyo Agung No. 12, Malang', 'note' => 'Design Sprint UI'],
                ['name' => 'Rizki Darmawan', 'gender' => 'Laki-laki', 'dob' => '2007-02-17', 'parent' => 'Darmawan Budi', 'addr' => 'Jl. Langsep No. 20, Malang', 'note' => 'Web Backend Developer'],
                ['name' => 'Tiara Lestari', 'gender' => 'Perempuan', 'dob' => '2008-11-03', 'parent' => 'Lestari Agus', 'addr' => 'Jl. Bandung Barat No. 3, Malang', 'note' => 'English Conversation'],
                ['name' => 'Aldi Kurniawan', 'gender' => 'Laki-laki', 'dob' => '2007-07-29', 'parent' => 'Kurniawan Eko', 'addr' => 'Jl. Bromo No. 18, Malang', 'note' => 'Frontend React'],
                ['name' => 'Fania Rahma', 'gender' => 'Perempuan', 'dob' => '2008-05-22', 'parent' => 'Rahman Hakim', 'addr' => 'Jl. Arjuno No. 9, Malang', 'note' => 'Design Prototyping'],
            ],
            'makassar' => [
                ['name' => 'Muhammad Fikri', 'gender' => 'Laki-laki', 'dob' => '2007-03-08', 'parent' => 'Fikri Hasan', 'addr' => 'Jl. Pettarani No. 110, Makassar', 'note' => 'Game Developer Unity'],
                ['name' => 'Nurul Fadilah', 'gender' => 'Perempuan', 'dob' => '2008-09-12', 'parent' => 'Fadil Rahman', 'addr' => 'Jl. Hertasning No. 34, Makassar', 'note' => 'Graphic Design Specialist'],
                ['name' => 'Ilham Syahputra', 'gender' => 'Laki-laki', 'dob' => '2007-11-25', 'parent' => 'Syahputra Amir', 'addr' => 'Jl. Perintis Kemerdekaan No. 50, Makassar', 'note' => 'C# & Game Engine'],
                ['name' => 'Aisyah Putri', 'gender' => 'Perempuan', 'dob' => '2008-04-03', 'parent' => 'Putra Mahardika', 'addr' => 'Jl. Sultan Alauddin No. 78, Makassar', 'note' => 'English Conversation'],
                ['name' => 'Fadel Muhammad', 'gender' => 'Laki-laki', 'dob' => '2009-06-17', 'parent' => 'Muhammad Dahlan', 'addr' => 'Jl. Boulevard No. 12, Panakkukang, Makassar', 'note' => 'Programming Dasar B'],
                ['name' => 'Siti Nurhaliza', 'gender' => 'Perempuan', 'dob' => '2008-01-29', 'parent' => 'Halim Sanusi', 'addr' => 'Jl. Pengayoman No. 9, Makassar', 'note' => 'Visual Branding & Vector'],
                ['name' => 'Rian Hidayat Jr', 'gender' => 'Laki-laki', 'dob' => '2007-08-14', 'parent' => 'Hidayatullah', 'addr' => 'Jl. Urip Sumoharjo No. 80, Makassar', 'note' => '3D Game Level Design'],
                ['name' => 'Zaskia Adelia', 'gender' => 'Perempuan', 'dob' => '2008-12-05', 'parent' => 'Adel Iskandar', 'addr' => 'Jl. Cenderawasih No. 22, Makassar', 'note' => 'English Pronunciation'],
                ['name' => 'Akbar Tanjung', 'gender' => 'Laki-laki', 'dob' => '2009-02-18', 'parent' => 'Tanjung Syafei', 'addr' => 'Jl. Veteran Selatan No. 44, Makassar', 'note' => 'Logic & Game Code'],
                ['name' => 'Putri Ayu Wandira', 'gender' => 'Perempuan', 'dob' => '2008-07-21', 'parent' => 'Wandi Rahman', 'addr' => 'Jl. Ratulangi No. 15, Makassar', 'note' => 'Design & Ilustrasi'],
                ['name' => 'Wahyu Pratama', 'gender' => 'Laki-laki', 'dob' => '2007-10-30', 'parent' => 'Pratama Said', 'addr' => 'Jl. Sungai Saddang No. 6, Makassar', 'note' => 'Unity C# Developer'],
                ['name' => 'Dinda Kirana', 'gender' => 'Perempuan', 'dob' => '2008-03-16', 'parent' => 'Kiran Basri', 'addr' => 'Jl. Gunung Bawakaraeng No. 99, Makassar', 'note' => 'English Speaking & Debate'],
                ['name' => 'Alif Ramadhan', 'gender' => 'Laki-laki', 'dob' => '2009-05-09', 'parent' => 'Ramadhan Daeng', 'addr' => 'Jl. Kakatua No. 31, Makassar', 'note' => 'Programming Dasar'],
                ['name' => 'Mutia Azzahra', 'gender' => 'Perempuan', 'dob' => '2008-11-11', 'parent' => 'Zahra Munawir', 'addr' => 'Jl. Kumala No. 18, Makassar', 'note' => 'Graphic Design Layout'],
                ['name' => 'Reza Pahlevi MKS', 'gender' => 'Laki-laki', 'dob' => '2007-09-04', 'parent' => 'Pahlevi Syamsul', 'addr' => 'Jl. Landak Baru No. 8, Makassar', 'note' => 'Game Assets & Logic'],
                ['name' => 'Naila Salsabila', 'gender' => 'Perempuan', 'dob' => '2008-02-27', 'parent' => 'Salsabil Bahar', 'addr' => 'Jl. Abdullah Daeng Sirua No. 102, Makassar', 'note' => 'English Fluency'],
                ['name' => 'Haikal Pratama', 'gender' => 'Laki-laki', 'dob' => '2009-08-19', 'parent' => 'Pratama Yusuf', 'addr' => 'Jl. Toddopuli No. 55, Makassar', 'note' => 'Python & C#'],
                ['name' => 'Salsabila Putri', 'gender' => 'Perempuan', 'dob' => '2008-06-13', 'parent' => 'Putra Mansyur', 'addr' => 'Jl. Emmy Saelan No. 27, Makassar', 'note' => 'Adobe Illustrator Vector'],
                ['name' => 'Fadli Rahman', 'gender' => 'Laki-laki', 'dob' => '2007-12-22', 'parent' => 'Rahman Azis', 'addr' => 'Jl. Antang Raya No. 40, Makassar', 'note' => 'Unity Physics & Animation'],
                ['name' => 'Khadijah Alwi', 'gender' => 'Perempuan', 'dob' => '2008-10-01', 'parent' => 'Alwi Shihab', 'addr' => 'Jl. Rajawali No. 19, Makassar', 'note' => 'English Conversation Group'],
                ['name' => 'Dwi Prasetyo', 'gender' => 'Laki-laki', 'dob' => '2009-03-24', 'parent' => 'Prasetyo Joko', 'addr' => 'Jl. Mappanyukki No. 14, Makassar', 'note' => 'Programming Dasar'],
                ['name' => 'Naurah Syakira', 'gender' => 'Perempuan', 'dob' => '2008-05-15', 'parent' => 'Syakir Anwar', 'addr' => 'Jl. Onta Lama No. 3, Makassar', 'note' => 'Poster & Brand Design'],
                ['name' => 'Bagus Setiawan', 'gender' => 'Laki-laki', 'dob' => '2007-04-09', 'parent' => 'Setiawan Hendro', 'addr' => 'Jl. Rappocini No. 67, Makassar', 'note' => 'Game Developer'],
                ['name' => 'Annisa Tri', 'gender' => 'Perempuan', 'dob' => '2008-08-30', 'parent' => 'Tri Wibowo', 'addr' => 'Jl. Mallengkeri No. 20, Makassar', 'note' => 'English Discussion'],
                ['name' => 'Rahmat Hidayatullah', 'gender' => 'Laki-laki', 'dob' => '2009-01-14', 'parent' => 'Hidayat Usman', 'addr' => 'Jl. Veteran Utara No. 81, Makassar', 'note' => 'Logic Programming'],
                ['name' => 'Fatimah Zahra', 'gender' => 'Perempuan', 'dob' => '2008-07-07', 'parent' => 'Zahrul Fuad', 'addr' => 'Jl. Andalas No. 12, Makassar', 'note' => 'Typography & Branding'],
                ['name' => 'Arif Budiman', 'gender' => 'Laki-laki', 'dob' => '2007-06-18', 'parent' => 'Budiman Slamet', 'addr' => 'Jl. Gunung Latimojong No. 45, Makassar', 'note' => 'Game Audio & Logic'],
                ['name' => 'Mega Utami', 'gender' => 'Perempuan', 'dob' => '2008-12-25', 'parent' => 'Utomo Seno', 'addr' => 'Jl. Jenderal Sudirman No. 10, Makassar', 'note' => 'English Conversation'],
            ],
            'semarang' => [
                ['name' => 'Satria Bima', 'gender' => 'Laki-laki', 'dob' => '2007-02-14', 'parent' => 'Bima Perkasa', 'addr' => 'Jl. Pandanaran No. 80, Semarang', 'note' => 'IELTS Academic 7.5+ Target'],
                ['name' => 'Anindya Kusuma', 'gender' => 'Perempuan', 'dob' => '2007-06-20', 'parent' => 'Kusuma Wardhana', 'addr' => 'Jl. Pemuda No. 102, Semarang', 'note' => 'TOEFL ITP 550+ Target'],
                ['name' => 'Bagus Wicaksono', 'gender' => 'Laki-laki', 'dob' => '2007-09-11', 'parent' => 'Wicaksono Harto', 'addr' => 'Jl. Gajahmada No. 45, Semarang', 'note' => 'Fullstack Web React/Node'],
                ['name' => 'Dwi Lestari', 'gender' => 'Perempuan', 'dob' => '2008-01-28', 'parent' => 'Lestari Suwarno', 'addr' => 'Jl. MT Haryono No. 560, Semarang', 'note' => 'Digital Concept Artist'],
                ['name' => 'Fajar Nugroho', 'gender' => 'Laki-laki', 'dob' => '2007-04-19', 'parent' => 'Nugroho Santoso', 'addr' => 'Jl. Pahlawan No. 15, Semarang', 'note' => 'Web App Architecture'],
                ['name' => 'Hanifah Zahra', 'gender' => 'Perempuan', 'dob' => '2007-11-04', 'parent' => 'Zahrul Imam', 'addr' => 'Jl. Majapahit No. 230, Semarang', 'note' => 'IELTS Writing Task 2'],
                ['name' => 'Kurnia Ramadhan', 'gender' => 'Laki-laki', 'dob' => '2007-08-12', 'parent' => 'Ramadhan Joko', 'addr' => 'Jl. Dr. Cipto No. 88, Semarang', 'note' => 'TOEFL Structure & Reading'],
                ['name' => 'Larasati Putri', 'gender' => 'Perempuan', 'dob' => '2008-05-17', 'parent' => 'Putra Sudarmo', 'addr' => 'Jl. Diponegoro No. 34, Semarang', 'note' => '2D Character Design'],
                ['name' => 'Mahendra Surya', 'gender' => 'Laki-laki', 'dob' => '2007-10-23', 'parent' => 'Surya Kencana', 'addr' => 'Jl. Kelud Raya No. 18, Semarang', 'note' => 'Fullstack Javascript'],
                ['name' => 'Nadine Aurelia', 'gender' => 'Perempuan', 'dob' => '2007-03-30', 'parent' => 'Aurelius Bambang', 'addr' => 'Jl. Menoreh Raya No. 9, Semarang', 'note' => 'IELTS Speaking & Essay'],
                ['name' => 'Panji Gemilang', 'gender' => 'Laki-laki', 'dob' => '2007-07-15', 'parent' => 'Gemilang Toto', 'addr' => 'Jl. Veteran No. 21, Semarang', 'note' => 'TOEFL Listening Strategies'],
                ['name' => 'Ratna Sari', 'gender' => 'Perempuan', 'dob' => '2008-09-08', 'parent' => 'Sari Gunawan', 'addr' => 'Jl. Sriwijaya No. 40, Semarang', 'note' => 'Digital Painting & Colors'],
                ['name' => 'Surya Saputra', 'gender' => 'Laki-laki', 'dob' => '2007-12-18', 'parent' => 'Saputra Haris', 'addr' => 'Jl. Imam Bonjol No. 150, Semarang', 'note' => 'Web API Integration'],
                ['name' => 'Tania Paramita', 'gender' => 'Perempuan', 'dob' => '2007-01-25', 'parent' => 'Paramita Agus', 'addr' => 'Jl. Siliwangi No. 300, Semarang', 'note' => 'IELTS Academic Writing'],
                ['name' => 'Viko Prasetyo', 'gender' => 'Laki-laki', 'dob' => '2007-05-09', 'parent' => 'Prasetyo Budi', 'addr' => 'Jl. Kaligarang No. 7, Semarang', 'note' => 'TOEFL Test Prep'],
                ['name' => 'Winda Safitri', 'gender' => 'Perempuan', 'dob' => '2008-08-14', 'parent' => 'Safitri Hendro', 'addr' => 'Jl. Pamularsih No. 62, Semarang', 'note' => 'Digital Illustration Art'],
                ['name' => 'Yoga Pratama', 'gender' => 'Laki-laki', 'dob' => '2007-11-01', 'parent' => 'Pratama Agus', 'addr' => 'Jl. Soekarno Hatta No. 80, Semarang', 'note' => 'Web Development'],
                ['name' => 'Zulfa Aulia', 'gender' => 'Perempuan', 'dob' => '2007-04-18', 'parent' => 'Aulia Rahman', 'addr' => 'Jl. Fatmawati No. 12, Semarang', 'note' => 'IELTS Vocabulary Mastery'],
                ['name' => 'Arya Sena', 'gender' => 'Laki-laki', 'dob' => '2007-07-21', 'parent' => 'Sena Wibowo', 'addr' => 'Jl. Medoho Raya No. 4, Semarang', 'note' => 'TOEFL Grammar Inversion'],
                ['name' => 'Bella Cantika', 'gender' => 'Perempuan', 'dob' => '2008-10-05', 'parent' => 'Cantika Yono', 'addr' => 'Jl. Tlogosari Raya No. 25, Semarang', 'note' => 'Character Concept Art'],
                ['name' => 'Candra Wijaya', 'gender' => 'Laki-laki', 'dob' => '2007-03-14', 'parent' => 'Wijaya Hendra', 'addr' => 'Jl. Supriyadi No. 19, Semarang', 'note' => 'Frontend React.js'],
                ['name' => 'Devi Anggraini', 'gender' => 'Perempuan', 'dob' => '2007-08-19', 'parent' => 'Anggoro Sigit', 'addr' => 'Jl. Wolter Monginsidi No. 50, Semarang', 'note' => 'IELTS Band 7.0'],
                ['name' => 'Erik Setiawan', 'gender' => 'Laki-laki', 'dob' => '2007-01-27', 'parent' => 'Setiawan Budi', 'addr' => 'Jl. Arteri Yos Sudarso No. 8, Semarang', 'note' => 'TOEFL Preparation'],
                ['name' => 'Febriana Putri', 'gender' => 'Perempuan', 'dob' => '2008-04-11', 'parent' => 'Putra Heri', 'addr' => 'Jl. Kedungmundu No. 90, Semarang', 'note' => 'Digital Sketch & Brush'],
                ['name' => 'Galih Rakasiwi', 'gender' => 'Laki-laki', 'dob' => '2007-02-17', 'parent' => 'Rakasiwi Tono', 'addr' => 'Jl. Tentara Pelajar No. 33, Semarang', 'note' => 'Web Fullstack Dev'],
                ['name' => 'Hesti Wulandari', 'gender' => 'Perempuan', 'dob' => '2007-11-03', 'parent' => 'Wibowo Seno', 'addr' => 'Jl. Sompok Lama No. 5, Semarang', 'note' => 'IELTS Reading Skills'],
                ['name' => 'Indra Lesmana', 'gender' => 'Laki-laki', 'dob' => '2007-07-29', 'parent' => 'Lesmana Dedi', 'addr' => 'Jl. MT Haryono No. 120, Semarang', 'note' => 'TOEFL Structure Practice'],
                ['name' => 'Juwita Permata', 'gender' => 'Perempuan', 'dob' => '2008-05-22', 'parent' => 'Permata Sigit', 'addr' => 'Jl. Sultan Agung No. 70, Semarang', 'note' => 'Concept Illustration'],
            ],
        ];

        $students = [];
        $studentCounter = 1;

        foreach ($studentNames as $branchKey => $list) {
            $branchObj = $branches[$branchKey];
            foreach ($list as $sData) {
                $code = strtoupper(substr($branchKey, 0, 3)).'-2026-'.str_pad((string) $studentCounter, 3, '0', STR_PAD_LEFT);
                $phone = '0812'.str_pad((string) (34567890 + $studentCounter * 37), 8, '0', STR_PAD_LEFT);
                $parentPhone = '0813'.str_pad((string) (87654321 - $studentCounter * 29), 8, '0', STR_PAD_LEFT);

                $student = Student::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchObj->id,
                    'name' => $sData['name'],
                    'gender' => $sData['gender'],
                    'date_of_birth' => $sData['dob'],
                    'parent_name' => $sData['parent'],
                    'parent_phone' => $parentPhone,
                    'phone' => $phone,
                    'address' => $sData['addr'],
                    'notes' => $sData['note'],
                    'joined_at' => '2026-01-05',
                    'status' => 'active',
                ]);

                $students[$branchKey][] = $student;
                $studentCounter++;
            }
        }

        // -------------------------------------------------------------
        // 8. Enrollments (Connecting Students to the 12 Classes)
        // -------------------------------------------------------------
        $branchClassMap = [
            'malang' => ['mlg_prog_a', 'mlg_web_dev', 'mlg_eng_spk', 'mlg_uiux_dsr'],
            'makassar' => ['mks_prog_b', 'mks_game_dev', 'mks_eng_conv', 'mks_graph_dsn'],
            'semarang' => ['smg_web_adv', 'smg_ielts_prep', 'smg_toefl_prep', 'smg_dig_illus'],
        ];

        $enrollments = [];
        $enrollmentsByClass = [];

        foreach ($students as $branchKey => $studentList) {
            $classKeys = $branchClassMap[$branchKey];
            foreach ($studentList as $idx => $student) {
                // Assign to 1 primary class
                $primaryClassKey = $classKeys[$idx % count($classKeys)];
                $primaryClass = $classes[$primaryClassKey];

                $enr1 = Enrollment::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'branch_id' => $primaryClass->branch_id,
                    'student_id' => $student->id,
                    'class_id' => $primaryClass->id,
                    'started_at' => '2026-01-05',
                    'status' => 'active',
                ]);

                $enrollments[] = $enr1;
                $enrollmentsByClass[$primaryClass->id][] = $enr1;

                // 25% of students enroll in a 2nd complementary class
                if ($idx % 4 === 0) {
                    $secClassKey = $classKeys[($idx + 1) % count($classKeys)];
                    $secClass = $classes[$secClassKey];

                    $enr2 = Enrollment::create([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenantId,
                        'branch_id' => $secClass->branch_id,
                        'student_id' => $student->id,
                        'class_id' => $secClass->id,
                        'started_at' => '2026-01-05',
                        'status' => 'active',
                    ]);

                    $enrollments[] = $enr2;
                    $enrollmentsByClass[$secClass->id][] = $enr2;
                }
            }
        }

        // -------------------------------------------------------------
        // 9. Teaching Sessions & Attendance Records (Jan 2026 - Oct 2026)
        // -------------------------------------------------------------
        $materialsByProgram = [
            'Programming' => [
                'Pengenalan Sintaks & Logika Percabangan',
                'Struktur Data Array, List & Dictionary',
                'Fungsi Modular & Parameterized Handling',
                'Pemrograman Berorientasi Objek (OOP) & Kelas',
                'DOM Manipulation & Event Listener Javascript',
                'RESTful API Integration & Async/Await',
                'State Management & Component Lifecycle',
                'Database Schema Design & ORM Querying',
                'Fullstack CRUD Project Implementation',
                'Debugging, Error Handling & Code Review',
                'Game Mechanics, Physics Engine & Collision',
                'Deployment, Git Workflow & CI/CD Pipeline',
            ],
            'English' => [
                'Ice Breaking, Pronunciation & Speaking Drill',
                'Expressing Opinions & Argumentative Discussions',
                'Idiomatic Expressions & Nuances in Dialogue',
                'Professional Presentation & Public Speaking',
                'IELTS Academic Writing Task 1 Data Analysis',
                'IELTS Writing Task 2 Argumentative Essays',
                'Listening Comprehension & Active Note Taking',
                'TOEFL Structure, Inversion & Parallelism',
                'Reading Speed, Skimming & Context Clues',
                'Mock Speaking Interview & Accent Reduction',
                'Business Communication, Emailing & Negotiations',
                'Full Mock Examination & Evaluation Review',
            ],
            'Design' => [
                'Prinsip Desain Grafis & Teori Warna Harmonis',
                'Tipografi, Skala Visual & Grid Layout System',
                'User Research, Persona & Information Architecture',
                'Wireframing & Low-Fidelity Prototyping Figma',
                'Design System, Tokens & Auto-Layout Figma',
                'Interactive Micro-Animations & User Flow',
                'Vector Mastery: Pen Tool & Complex Shapes',
                'Brand Identity Creation & Visual Guidelines',
                'Digital Illustration: Line Art, Shading & Light',
                'Composition, Brush Textures & Concept Art',
                'Usability Testing & Design Iteration',
                'Final Design Portfolio & Case Study Showcase',
            ],
        ];

        $startDate = Carbon::parse('2026-01-05');
        $endDate = Carbon::parse('2026-10-31');
        $today = Carbon::parse('2026-10-05');

        $sessionSequence = 0;
        $teachingSessions = [];
        $replacementCount = 0;

        $currDate = $startDate->copy();
        while ($currDate->lte($endDate)) {
            $dayOfWeek = $currDate->dayOfWeek; // 0 (Sun) to 6 (Sat)

            foreach ($allSchedules as $schInfo) {
                if ($schInfo['day_of_week'] === $dayOfWeek) {
                    $sessionDateStr = $currDate->toDateString();
                    $isPast = $currDate->lt($today);

                    $status = 'scheduled';
                    if ($isPast) {
                        // 97% completed, 3% cancelled
                        $status = ($sessionSequence % 31 === 0) ? 'cancelled' : 'completed';
                    }

                    // Actual tutor resolution
                    $actualTutor = $schInfo['primary_tutor'];
                    $hasSubstitute = false;

                    if ($status === 'completed' && ! empty($schInfo['secondary_tutor_ids']) && ($sessionSequence % 9 === 3) && $replacementCount < 8) {
                        $subId = $schInfo['secondary_tutor_ids'][0];
                        $actualTutor = User::find($subId) ?? $schInfo['primary_tutor'];
                        $hasSubstitute = true;
                        $replacementCount++;
                    }

                    // Material selection
                    $matList = $materialsByProgram[$schInfo['subject']] ?? $materialsByProgram['Programming'];
                    $matIndex = $sessionSequence % count($matList);
                    $materialTitle = $matList[$matIndex];

                    $notes = match ($status) {
                        'completed' => 'Materi disampaikan tuntas. Seluruh siswa aktif mengikuti praktik dan bedah studi kasus.',
                        'cancelled' => 'Sesi digeser karena pemeliharaan ruang studio / penyesuaian agenda bimbel.',
                        default => 'Sesi pembelajaran reguler terjadwal.',
                    };

                    $session = TeachingSession::create([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => $tenantId,
                        'branch_id' => $schInfo['branch']->id,
                        'schedule_id' => $schInfo['schedule']->id,
                        'class_id' => $schInfo['class']->id,
                        'scheduled_tutor_id' => $schInfo['primary_tutor']->id,
                        'actual_tutor_id' => $status === 'completed' ? $actualTutor->id : null,
                        'session_date' => $sessionDateStr,
                        'start_time' => $schInfo['start_time'],
                        'end_time' => $schInfo['end_time'],
                        'room' => $schInfo['room'],
                        'status' => $status,
                        'material' => $status === 'completed' ? $materialTitle : null,
                        'notes' => $notes,
                    ]);

                    $teachingSessions[] = $session;

                    // Tutor Replacement Record if substitute
                    if ($hasSubstitute) {
                        TutorReplacement::create([
                            'id' => (string) Str::uuid(),
                            'tenant_id' => $tenantId,
                            'branch_id' => $schInfo['branch']->id,
                            'teaching_session_id' => $session->id,
                            'scheduled_tutor_id' => $schInfo['primary_tutor']->id,
                            'previous_actual_tutor_id' => null,
                            'replacement_tutor_id' => $actualTutor->id,
                            'reason' => 'Tutor utama berhalangan hadir karena dinas riset akademik / seminar nasional.',
                            'changed_by' => $owner->id,
                            'changed_at' => $currDate->copy()->setTime(14, 0)->toDateTimeString(),
                        ]);
                    }

                    // Record attendances for completed sessions
                    if ($status === 'completed') {
                        $endTimeObj = Carbon::parse($sessionDateStr.' '.$schInfo['end_time']);

                        // Tutor Attendance
                        TutorAttendance::create([
                            'id' => (string) Str::uuid(),
                            'tenant_id' => $tenantId,
                            'branch_id' => $schInfo['branch']->id,
                            'teaching_session_id' => $session->id,
                            'tutor_id' => $actualTutor->id,
                            'status' => 'present',
                            'recorded_at' => $endTimeObj,
                            'source' => 'student_attendance_submission',
                        ]);

                        // Student Attendances
                        $enrolledInClass = $enrollmentsByClass[$schInfo['class']->id] ?? [];
                        foreach ($enrolledInClass as $sIdx => $enr) {
                            $rand = ($sessionSequence * 17 + $sIdx * 23) % 100;
                            if ($rand < 88) {
                                $attStatus = 'hadir';
                                $attNote = null;
                            } elseif ($rand < 93) {
                                $attStatus = 'izin';
                                $attNote = 'Izin urusan keluarga / kegiatan sekolah';
                            } elseif ($rand < 97) {
                                $attStatus = 'sakit';
                                $attNote = 'Sakit flu/demam';
                            } else {
                                $attStatus = 'alpa';
                                $attNote = 'Tanpa keterangan';
                            }

                            StudentAttendance::create([
                                'id' => (string) Str::uuid(),
                                'tenant_id' => $tenantId,
                                'branch_id' => $schInfo['branch']->id,
                                'teaching_session_id' => $session->id,
                                'student_id' => $enr->student_id,
                                'status' => $attStatus,
                                'note' => $attNote,
                                'recorded_at' => $endTimeObj,
                                'recorded_by' => $actualTutor->id,
                            ]);
                        }
                    }

                    $sessionSequence++;
                }
            }

            $currDate->addDay();
        }

        // -------------------------------------------------------------
        // 10. Assessments & Results for the 3 Core Programs
        // -------------------------------------------------------------
        $assessmentsConfig = [
            ['class_key' => 'mlg_prog_a', 'name' => 'Kuis 1: Logika Percabangan & Array Python', 'type' => 'quiz', 'date' => '2026-08-24', 'material' => 'Struktur Data Dasar', 'max' => 100],
            ['class_key' => 'mlg_web_dev', 'name' => 'Tugas Proyek: Landing Page Responsif', 'type' => 'tugas', 'date' => '2026-09-16', 'material' => 'HTML5, Tailwind & Flexbox', 'max' => 100],
            ['class_key' => 'mlg_eng_spk', 'name' => 'Speaking Evaluation: Public Debate', 'type' => 'ujian', 'date' => '2026-09-22', 'material' => 'Fluency & Argument Structure', 'max' => 100],
            ['class_key' => 'mlg_uiux_dsr', 'name' => 'Figma Prototyping: Mobile Food App', 'type' => 'tugas', 'date' => '2026-09-25', 'material' => 'Wireframing & Components', 'max' => 100],

            ['class_key' => 'mks_prog_b', 'name' => 'Kuis 1: Modular Functions & Logic', 'type' => 'quiz', 'date' => '2026-08-25', 'material' => 'Function & Parameter Handling', 'max' => 100],
            ['class_key' => 'mks_game_dev', 'name' => 'Proyek Mini: 2D Platformer Mechanics', 'type' => 'tugas', 'date' => '2026-09-21', 'material' => 'Unity C# Scripts & Rigidbody', 'max' => 100],
            ['class_key' => 'mks_eng_conv', 'name' => 'Dialogue Evaluation: Daily Negotiation', 'type' => 'quiz', 'date' => '2026-09-17', 'material' => 'Idiomatic Expressions', 'max' => 100],
            ['class_key' => 'mks_graph_dsn', 'name' => 'Brand Identity: Vector Logo & Palette', 'type' => 'tugas', 'date' => '2026-09-19', 'material' => 'Adobe Illustrator Vector', 'max' => 100],

            ['class_key' => 'smg_web_adv', 'name' => 'Ujian Tengah: REST API Fullstack App', 'type' => 'ujian', 'date' => '2026-09-21', 'material' => 'React Frontend & Node Backend', 'max' => 100],
            ['class_key' => 'smg_ielts_prep', 'name' => 'Mock Test: IELTS Academic Task 2', 'type' => 'ujian', 'date' => '2026-09-23', 'material' => 'Academic Essay Writing', 'max' => 100],
            ['class_key' => 'smg_toefl_prep', 'name' => 'Diagnostic Test: TOEFL Structure & Grammar', 'type' => 'ujian', 'date' => '2026-09-22', 'material' => 'Inversion, Parallelism & Clauses', 'max' => 100],
            ['class_key' => 'smg_dig_illus', 'name' => 'Karya Digital: Character Concept Art', 'type' => 'tugas', 'date' => '2026-09-26', 'material' => 'Lighting, Shading & Brushwork', 'max' => 100],
        ];

        foreach ($assessmentsConfig as $aIdx => $aCfg) {
            $class = $classes[$aCfg['class_key']];
            $tutor = $class->tutorAssignments()->first()?->tutor ?? $owner;

            $assessment = Assessment::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'branch_id' => $class->branch_id,
                'class_id' => $class->id,
                'name' => $aCfg['name'],
                'type' => $aCfg['type'],
                'material' => $aCfg['material'],
                'assessment_date' => $aCfg['date'],
                'max_score' => $aCfg['max'],
                'notes' => 'Penilaian berkala penguasaan materi praktis dan teori.',
                'created_by' => $tutor->id,
            ]);

            $enrolled = $enrollmentsByClass[$class->id] ?? [];
            foreach ($enrolled as $eIdx => $enr) {
                $scoreBase = 74 + (($aIdx * 7 + $eIdx * 11) % 24);
                $scoreNote = match (true) {
                    $scoreBase >= 90 => 'Karya dan pemahaman konsep sangat memuaskan.',
                    $scoreBase >= 80 => 'Kualitas pengerjaan baik dan terstruktur rapi.',
                    default => 'Cukup baik, pertahankan konsistensi latihan studi kasus.',
                };

                AssessmentResult::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'branch_id' => $class->branch_id,
                    'assessment_id' => $assessment->id,
                    'student_id' => $enr->student_id,
                    'score' => $scoreBase,
                    'notes' => $scoreNote,
                ]);
            }
        }

        // -------------------------------------------------------------
        // 11. Payments / Invoices (Historical from January 2026 to October 2026)
        // -------------------------------------------------------------
        $periods = [
            '2026-01' => ['due' => '2026-01-10', 'is_current' => false],
            '2026-02' => ['due' => '2026-02-10', 'is_current' => false],
            '2026-03' => ['due' => '2026-03-10', 'is_current' => false],
            '2026-04' => ['due' => '2026-04-10', 'is_current' => false],
            '2026-05' => ['due' => '2026-05-10', 'is_current' => false],
            '2026-06' => ['due' => '2026-06-10', 'is_current' => false],
            '2026-07' => ['due' => '2026-07-10', 'is_current' => false],
            '2026-08' => ['due' => '2026-08-10', 'is_current' => false],
            '2026-09' => ['due' => '2026-09-10', 'is_current' => false],
            '2026-10' => ['due' => '2026-10-10', 'is_current' => true],
        ];

        $paymentMethods = [
            'Transfer Bank BCA',
            'Transfer Bank Mandiri',
            'Transfer Bank BNI',
            'Transfer Bank BRI',
            'QRIS Gateway',
            'Tunai di Kasir Cabang',
        ];

        $adminMap = [
            $branchMalang->id => $adminMalang,
            $branchMakassar->id => $adminMakassar,
            $branchSemarang->id => $adminSemarang,
        ];

        $paymentCounter = 0;
        foreach ($periods as $periodCode => $pInfo) {
            foreach ($enrollments as $enr) {
                $branchAdmin = $adminMap[$enr->branch_id] ?? $adminMalang;
                $feeAmount = $classFeeMap[$enr->class_id] ?? 650000.00;
                $methodStr = $paymentMethods[$paymentCounter % count($paymentMethods)];

                if (! $pInfo['is_current']) {
                    // Past months (Jan - Sep): 93% lunas, 7% terlambat
                    $isOverdue = ($paymentCounter % 15 === 0);
                    if ($isOverdue) {
                        Payment::create([
                            'id' => (string) Str::uuid(),
                            'tenant_id' => $tenantId,
                            'branch_id' => $enr->branch_id,
                            'student_id' => $enr->student_id,
                            'enrollment_id' => $enr->id,
                            'period' => $periodCode,
                            'amount' => $feeAmount,
                            'due_date' => $pInfo['due'],
                            'paid_at' => null,
                            'status' => 'terlambat',
                            'notes' => 'Tunggakan kursus - Reminder WhatsApp dikirimkan admin.',
                            'recorded_by' => $branchAdmin->id,
                        ]);
                    } else {
                        $paidDay = 1 + ($paymentCounter % 9);
                        $paidAt = Carbon::parse("{$periodCode}-".str_pad((string) $paidDay, 2, '0', STR_PAD_LEFT).' 10:30:00');

                        Payment::create([
                            'id' => (string) Str::uuid(),
                            'tenant_id' => $tenantId,
                            'branch_id' => $enr->branch_id,
                            'student_id' => $enr->student_id,
                            'enrollment_id' => $enr->id,
                            'period' => $periodCode,
                            'amount' => $feeAmount,
                            'due_date' => $pInfo['due'],
                            'paid_at' => $paidAt,
                            'status' => 'lunas',
                            'notes' => "Pembayaran lunas via {$methodStr}.",
                            'recorded_by' => $branchAdmin->id,
                        ]);
                    }
                } else {
                    // Current Month (2026-10): 55% lunas, 20% menunggu_verifikasi, 15% belum_bayar, 10% partial
                    $mod = $paymentCounter % 10;
                    if ($mod < 5) {
                        $paidAt = Carbon::parse('2026-10-0'.(1 + ($paymentCounter % 4)).' 14:15:00');
                        Payment::create([
                            'id' => (string) Str::uuid(),
                            'tenant_id' => $tenantId,
                            'branch_id' => $enr->branch_id,
                            'student_id' => $enr->student_id,
                            'enrollment_id' => $enr->id,
                            'period' => $periodCode,
                            'amount' => $feeAmount,
                            'due_date' => $pInfo['due'],
                            'paid_at' => $paidAt,
                            'status' => 'lunas',
                            'notes' => "Pembayaran SPP Oktober lunas via {$methodStr}.",
                            'recorded_by' => $branchAdmin->id,
                        ]);
                    } elseif ($mod < 7) {
                        Payment::create([
                            'id' => (string) Str::uuid(),
                            'tenant_id' => $tenantId,
                            'branch_id' => $enr->branch_id,
                            'student_id' => $enr->student_id,
                            'enrollment_id' => $enr->id,
                            'period' => $periodCode,
                            'amount' => $feeAmount,
                            'due_date' => $pInfo['due'],
                            'paid_at' => null,
                            'status' => 'menunggu_verifikasi',
                            'notes' => "Bukti transfer {$methodStr} telah diunggah orang tua.",
                            'recorded_by' => $branchAdmin->id,
                        ]);
                    } elseif ($mod < 9) {
                        Payment::create([
                            'id' => (string) Str::uuid(),
                            'tenant_id' => $tenantId,
                            'branch_id' => $enr->branch_id,
                            'student_id' => $enr->student_id,
                            'enrollment_id' => $enr->id,
                            'period' => $periodCode,
                            'amount' => $feeAmount,
                            'due_date' => $pInfo['due'],
                            'paid_at' => null,
                            'status' => 'belum_bayar',
                            'notes' => 'Tagihan reguler periode berjalan.',
                            'recorded_by' => $branchAdmin->id,
                        ]);
                    } else {
                        $halfAmount = $feeAmount / 2;
                        Payment::create([
                            'id' => (string) Str::uuid(),
                            'tenant_id' => $tenantId,
                            'branch_id' => $enr->branch_id,
                            'student_id' => $enr->student_id,
                            'enrollment_id' => $enr->id,
                            'period' => $periodCode,
                            'amount' => $halfAmount,
                            'due_date' => $pInfo['due'],
                            'paid_at' => Carbon::parse('2026-10-02 09:00:00'),
                            'status' => 'lunas',
                            'notes' => 'Pembayaran Cicilan 1 (Rp '.number_format($halfAmount, 0, ',', '.').") via {$methodStr}.",
                            'recorded_by' => $branchAdmin->id,
                        ]);

                        Payment::create([
                            'id' => (string) Str::uuid(),
                            'tenant_id' => $tenantId,
                            'branch_id' => $enr->branch_id,
                            'student_id' => $enr->student_id,
                            'enrollment_id' => $enr->id,
                            'period' => "{$periodCode} (Sisa)",
                            'amount' => $halfAmount,
                            'due_date' => '2026-10-25',
                            'paid_at' => null,
                            'status' => 'belum_bayar',
                            'notes' => 'Sisa tagihan cicilan 2 jatuh tempo 25 Oktober 2026.',
                            'recorded_by' => $branchAdmin->id,
                        ]);
                    }
                }

                $paymentCounter++;
            }
        }

        // -------------------------------------------------------------
        // 12. Honor Calculations (Jan - Sep Finalized/Paid + Oct Draft)
        // -------------------------------------------------------------
        $months = [
            ['start' => '2026-01-01', 'end' => '2026-01-31', 'status' => 'paid', 'paid_at' => '2026-02-02 10:00:00', 'finalized_at' => '2026-02-01 16:00:00'],
            ['start' => '2026-02-01', 'end' => '2026-02-28', 'status' => 'paid', 'paid_at' => '2026-03-02 10:00:00', 'finalized_at' => '2026-03-01 16:00:00'],
            ['start' => '2026-03-01', 'end' => '2026-03-31', 'status' => 'paid', 'paid_at' => '2026-04-02 10:00:00', 'finalized_at' => '2026-04-01 16:00:00'],
            ['start' => '2026-04-01', 'end' => '2026-04-30', 'status' => 'paid', 'paid_at' => '2026-05-02 10:00:00', 'finalized_at' => '2026-05-01 16:00:00'],
            ['start' => '2026-05-01', 'end' => '2026-05-31', 'status' => 'paid', 'paid_at' => '2026-06-02 10:00:00', 'finalized_at' => '2026-06-01 16:00:00'],
            ['start' => '2026-06-01', 'end' => '2026-06-30', 'status' => 'paid', 'paid_at' => '2026-07-02 10:00:00', 'finalized_at' => '2026-07-01 16:00:00'],
            ['start' => '2026-07-01', 'end' => '2026-07-31', 'status' => 'paid', 'paid_at' => '2026-08-02 10:00:00', 'finalized_at' => '2026-08-01 16:00:00'],
            ['start' => '2026-08-01', 'end' => '2026-08-31', 'status' => 'paid', 'paid_at' => '2026-09-02 10:00:00', 'finalized_at' => '2026-09-01 16:00:00'],
            ['start' => '2026-09-01', 'end' => '2026-09-30', 'status' => 'paid', 'paid_at' => '2026-10-02 11:00:00', 'finalized_at' => '2026-10-01 17:00:00'],
            ['start' => '2026-10-01', 'end' => '2026-10-31', 'status' => 'draft', 'paid_at' => null, 'finalized_at' => null],
        ];

        foreach ($months as $hPeriod) {
            foreach ($tutors as $tKey => $tutorObj) {
                $scheme = match ($tKey) {
                    'dewi' => $schemeSenior,
                    'putri', 'kevin' => $schemeSpecialist,
                    'rian' => $schemeKoordinator,
                    default => $schemeReguler,
                };

                $sessionCount = TeachingSession::where('tenant_id', $tenantId)
                    ->where('actual_tutor_id', $tutorObj->id)
                    ->where('status', 'completed')
                    ->whereBetween('session_date', [$hPeriod['start'], $hPeriod['end']])
                    ->count();

                if ($sessionCount === 0 && $hPeriod['status'] === 'draft') {
                    $sessionCount = 3;
                } elseif ($sessionCount === 0) {
                    $sessionCount = 8;
                }

                $baseAmount = match ($scheme->method) {
                    'fixed_monthly' => 3000000.00 + ($sessionCount * 100000.00),
                    default => $sessionCount * (float) $scheme->rate,
                };

                $adjAmount = ($tKey === 'dewi' || $tKey === 'putri') && $hPeriod['status'] === 'paid' ? 150000.00 : 0.0;
                $adjReason = $adjAmount > 0 ? 'Bonus apresiasi pengajaran berkinerja tinggi dan ulasan positif siswa.' : null;
                $finalAmount = $baseAmount + $adjAmount;

                $branchId = match ($tKey) {
                    'kevin', 'maya' => $branchMakassar->id,
                    'putri', 'sarah', 'bambang' => $branchSemarang->id,
                    default => $branchMalang->id,
                };

                $calcAdmin = $adminMap[$branchId] ?? $adminMalang;

                HonorCalculation::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'tutor_id' => $tutorObj->id,
                    'honor_scheme_id' => $scheme->id,
                    'period_start' => $hPeriod['start'],
                    'period_end' => $hPeriod['end'],
                    'method' => $scheme->method,
                    'base_amount' => $baseAmount,
                    'adjustment_amount' => $adjAmount,
                    'final_amount' => $finalAmount,
                    'status' => $hPeriod['status'],
                    'adjustment_reason' => $adjReason,
                    'finalized_at' => $hPeriod['finalized_at'],
                    'paid_at' => $hPeriod['paid_at'],
                    'calculated_by' => $calcAdmin->id,
                    'finalized_by' => $hPeriod['finalized_at'] ? $owner->id : null,
                ]);
            }
        }

        // -------------------------------------------------------------
        // 13. Audit Logs
        // -------------------------------------------------------------
        $auditActions = [
            [
                'actor' => $adminMalang,
                'action' => 'payment.record',
                'entity_type' => Payment::class,
                'new_values' => ['status' => 'lunas', 'amount' => 750000.00],
                'metadata' => ['method' => 'manual_transfer', 'branch' => 'Malang'],
                'created_at' => now()->subDays(2),
            ],
            [
                'actor' => $adminMakassar,
                'action' => 'payment.verify',
                'entity_type' => Payment::class,
                'new_values' => ['status' => 'lunas'],
                'metadata' => ['verified_by' => 'Rizky Ramadhan', 'bank' => 'Mandiri'],
                'created_at' => now()->subDays(3),
            ],
            [
                'actor' => $owner,
                'action' => 'honor.finalize',
                'entity_type' => HonorCalculation::class,
                'new_values' => ['status' => 'paid', 'period' => '2026-09'],
                'metadata' => ['payroll_cycle' => 'September 2026'],
                'created_at' => now()->subDays(4),
            ],
            [
                'actor' => $tutors['rian'],
                'action' => 'attendance.submit',
                'entity_type' => TeachingSession::class,
                'new_values' => ['present_count' => 12, 'status' => 'completed'],
                'metadata' => ['class' => 'Programming Dasar A'],
                'created_at' => now()->subDays(1),
            ],
            [
                'actor' => $tutors['dimas'],
                'action' => 'assessment.create',
                'entity_type' => Assessment::class,
                'new_values' => ['name' => 'Figma Prototyping', 'max_score' => 100],
                'metadata' => ['class' => 'UI/UX Design Dasar'],
                'created_at' => now()->subDays(5),
            ],
            [
                'actor' => $adminSemarang,
                'action' => 'student.create',
                'entity_type' => Student::class,
                'new_values' => ['name' => 'Satria Bima', 'branch' => 'Semarang'],
                'metadata' => ['program' => 'English'],
                'created_at' => now()->subDays(10),
            ],
        ];

        foreach ($auditActions as $log) {
            AuditLog::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenantId,
                'actor_user_id' => $log['actor']->id,
                'action' => $log['action'],
                'entity_type' => $log['entity_type'],
                'entity_id' => Str::uuid(),
                'old_values' => null,
                'new_values' => $log['new_values'],
                'metadata' => $log['metadata'],
                'created_at' => $log['created_at'],
            ]);
        }
    }

    /**
     * Clean all existing records for the given tenant ID in reverse foreign-key dependency order.
     */
    protected function cleanExistingTenantData(string $tenantId): void
    {
        // 1. Audit Logs
        AuditLog::where('tenant_id', $tenantId)->delete();

        // 2. Assessment Results & Assessments
        AssessmentResult::where('tenant_id', $tenantId)->delete();
        Assessment::where('tenant_id', $tenantId)->delete();

        // 3. Honor Calculations & Honor Assignments & Honor Schemes
        HonorCalculation::where('tenant_id', $tenantId)->delete();
        HonorAssignment::where('tenant_id', $tenantId)->delete();
        HonorScheme::where('tenant_id', $tenantId)->delete();

        // 4. Payments
        Payment::where('tenant_id', $tenantId)->delete();

        // 5. Tutor Replacements, Tutor Attendances, Student Attendances
        TutorReplacement::where('tenant_id', $tenantId)->delete();
        TutorAttendance::where('tenant_id', $tenantId)->delete();
        StudentAttendance::where('tenant_id', $tenantId)->delete();

        // 6. Teaching Sessions
        TeachingSession::where('tenant_id', $tenantId)->delete();

        // 7. Recurring Schedules
        Schedule::where('tenant_id', $tenantId)->delete();

        // 8. Tutor Assignments
        TutorAssignment::where('tenant_id', $tenantId)->delete();

        // 9. Enrollments
        Enrollment::where('tenant_id', $tenantId)->delete();

        // 10. Classes
        Classes::where('tenant_id', $tenantId)->delete();

        // 11. Students
        Student::where('tenant_id', $tenantId)->delete();

        // 12. BranchUser & Branches
        BranchUser::where('tenant_id', $tenantId)->delete();
        Branch::where('tenant_id', $tenantId)->delete();

        // 13. Tenant Users (Owner, Admins, Tutors)
        User::where('tenant_id', $tenantId)->delete();

        // 14. Tenant Settings
        TenantSetting::where('tenant_id', $tenantId)->delete();
    }
}
