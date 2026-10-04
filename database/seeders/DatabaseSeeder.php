<?php

namespace Database\Seeders;

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
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin (platform-scoped, tenant_id = null)
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

        // 2. Tenant: Prime Academy Tutoring Center
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Prime Academy Tutoring Center',
            'slug' => 'prime-academy',
            'status' => 'active',
            'logo' => null,
            'description' => 'Bimbingan Belajar Unggulan SD, SMP, SMA & Persiapan UTBK SNBT',
            'address' => 'Jl. Ijen No. 12, Malang, Jawa Timur',
            'phone' => '0341-551234',
            'email' => 'admin@primeacademy.id',
        ]);

        // Tenant Settings
        TenantSetting::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'key' => 'payment_due_day',
            'value' => ['day' => 10],
        ]);

        TenantSetting::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'key' => 'timezone',
            'value' => ['timezone' => 'Asia/Jakarta'],
        ]);

        TenantSetting::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'key' => 'currency',
            'value' => ['code' => 'IDR', 'symbol' => 'Rp'],
        ]);

        // 3. Branches across Indonesia (Malang, Makassar, Semarang, Bandung)
        $branchMalang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Prime Academy - Malang',
            'code' => 'MLG-01',
            'address' => 'Jl. Ijen No. 12, Kota Malang',
            'phone' => '0341-551234',
            'status' => 'active',
        ]);

        $branchMakassar = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Prime Academy - Makassar',
            'code' => 'MKS-01',
            'address' => 'Jl. AP Pettarani No. 88, Makassar',
            'phone' => '0411-884567',
            'status' => 'active',
        ]);

        $branchSemarang = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Prime Academy - Semarang',
            'code' => 'SMG-01',
            'address' => 'Jl. Pandanaran No. 45, Semarang',
            'phone' => '024-8419876',
            'status' => 'active',
        ]);

        $branchBandung = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Prime Academy - Bandung',
            'code' => 'BDG-01',
            'address' => 'Jl. Ir. H. Djuanda (Dago) No. 24, Bandung',
            'phone' => '022-4231122',
            'status' => 'active',
        ]);

        // 4. Users
        $owner = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Budi Pratama',
            'email' => 'owner@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'owner',
            'status' => 'active',
        ]);

        $adminMalang = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Siti Rahmawati',
            'email' => 'admin.malang@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $adminMakassar = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Rizky Ramadhan',
            'email' => 'admin.makassar@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $adminSemarang = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Nurul Aini',
            'email' => 'admin.semarang@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $tutorAhmad = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Ahmad Fauzi, S.Pd.',
            'email' => 'tutor.ahmad@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $tutorDewi = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Dewi Lestari, M.Si.',
            'email' => 'tutor.dewi@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $tutorBambang = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Bambang Wijaya, S.Si.',
            'email' => 'tutor.bambang@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        $tutorSarah = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Sarah Wijaya, S.Pd.',
            'email' => 'tutor.sarah@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'tutor',
            'status' => 'active',
        ]);

        // 5. BranchUser explicit assignments for Admins
        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'user_id' => $adminMalang->id,
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMakassar->id,
            'user_id' => $adminMakassar->id,
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchSemarang->id,
            'user_id' => $adminSemarang->id,
        ]);

        // 6. Students across branches
        $student1 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'name' => 'Andi Pratama',
            'date_of_birth' => '2008-05-14',
            'gender' => 'Laki-laki',
            'phone' => '081311223344',
            'address' => 'Jl. Ijen No. 45, Malang',
            'parent_name' => 'Joko Pratama',
            'parent_phone' => '081299001122',
            'notes' => 'Target masuk ITB Teknik Informatika',
            'joined_at' => now()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        $student2 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'name' => 'Nadia Amanda',
            'date_of_birth' => '2008-08-20',
            'gender' => 'Perempuan',
            'phone' => '081322334455',
            'address' => 'Jl. Soekarno Hatta No. 22, Malang',
            'parent_name' => 'Hendra Amanda',
            'parent_phone' => '081299003344',
            'notes' => 'Target masuk Kedokteran UB',
            'joined_at' => now()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        $student3 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMakassar->id,
            'name' => 'Fajar Nugraha',
            'date_of_birth' => '2008-11-03',
            'gender' => 'Laki-laki',
            'phone' => '081333445566',
            'address' => 'Jl. Pettarani No. 5, Makassar',
            'parent_name' => 'Agus Setiawan',
            'parent_phone' => '081299005566',
            'notes' => 'Perlu bimbingan ekstra Matematika',
            'joined_at' => now()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        $student4 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchSemarang->id,
            'name' => 'Rina Marlina',
            'date_of_birth' => '2011-03-25',
            'gender' => 'Perempuan',
            'phone' => '081344556677',
            'address' => 'Jl. Pemuda No. 8, Semarang',
            'parent_name' => 'Bambang Marlina',
            'parent_phone' => '081299007788',
            'notes' => 'Siswa SMP berprestasi',
            'joined_at' => now()->subMonth()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        $student5 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchBandung->id,
            'name' => 'Aisyah Rahma',
            'date_of_birth' => '2011-07-19',
            'gender' => 'Perempuan',
            'phone' => '081355667788',
            'address' => 'Jl. Dago No. 10, Bandung',
            'parent_name' => 'Dedi Rahma',
            'parent_phone' => '081299009900',
            'notes' => 'Persiapan ujian sekolah & olimpiade',
            'joined_at' => now()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        // 7. Classes
        $classMatematika = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'name' => '12 IPA - Matematika Intensif',
            'subject' => 'Matematika',
            'level' => '12 SMA',
            'capacity' => 15,
            'status' => 'active',
        ]);

        $classFisika = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'name' => '12 IPA - Fisika UTBK',
            'subject' => 'Fisika',
            'level' => '12 SMA',
            'capacity' => 15,
            'status' => 'active',
        ]);

        $classInggris = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMakassar->id,
            'name' => '9 SMP - Bahasa Inggris Reguler',
            'subject' => 'Bahasa Inggris',
            'level' => '9 SMP',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $classKimia = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchSemarang->id,
            'name' => '11 IPA - Kimia Dasar',
            'subject' => 'Kimia',
            'level' => '11 SMA',
            'capacity' => 18,
            'status' => 'active',
        ]);

        // 8. Enrollments
        $enrollment1 = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'student_id' => $student1->id,
            'class_id' => $classMatematika->id,
            'started_at' => now()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        $enrollment2 = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'student_id' => $student2->id,
            'class_id' => $classMatematika->id,
            'started_at' => now()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        $enrollment3 = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMakassar->id,
            'student_id' => $student3->id,
            'class_id' => $classInggris->id,
            'started_at' => now()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        $enrollment4 = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchSemarang->id,
            'student_id' => $student4->id,
            'class_id' => $classKimia->id,
            'started_at' => now()->subMonth()->startOfMonth()->toDateString(),
            'status' => 'active',
        ]);

        // 9. Tutor Assignments
        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'tutor_id' => $tutorAhmad->id,
            'class_id' => $classMatematika->id,
            'started_at' => now()->startOfYear()->toDateString(),
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'tutor_id' => $tutorDewi->id,
            'class_id' => $classFisika->id,
            'started_at' => now()->startOfYear()->toDateString(),
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMakassar->id,
            'tutor_id' => $tutorBambang->id,
            'class_id' => $classInggris->id,
            'started_at' => now()->startOfYear()->toDateString(),
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchSemarang->id,
            'tutor_id' => $tutorSarah->id,
            'class_id' => $classKimia->id,
            'started_at' => now()->startOfYear()->toDateString(),
            'status' => 'active',
        ]);

        // 10. Honor Schemes
        $honorSchemeReguler = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Skema Honor Reguler Per Sesi',
            'method' => 'per_session',
            'rate' => 150000.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        $honorSchemeSenior = HonorScheme::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Skema Honor Senior Tutor Per Sesi',
            'method' => 'per_session',
            'rate' => 200000.00,
            'effective_from' => '2026-01-01',
            'status' => 'active',
            'created_by' => $owner->id,
        ]);

        // 11. Honor Assignments
        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'tutor_id' => null,
            'honor_scheme_id' => $honorSchemeReguler->id,
            'assignment_type' => 'default',
            'effective_from' => '2026-01-01',
        ]);

        HonorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'tutor_id' => $tutorDewi->id,
            'honor_scheme_id' => $honorSchemeSenior->id,
            'assignment_type' => 'tutor_override',
            'effective_from' => '2026-01-01',
        ]);

        // 12. Recurring Schedules
        $schedule1 = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'class_id' => $classMatematika->id,
            'scheduled_tutor_id' => $tutorAhmad->id,
            'day_of_week' => 1,
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang M-1',
            'starts_on' => now()->startOfYear()->toDateString(),
            'status' => 'active',
        ]);

        $schedule2 = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'class_id' => $classFisika->id,
            'scheduled_tutor_id' => $tutorDewi->id,
            'day_of_week' => 3,
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang M-2',
            'starts_on' => now()->startOfYear()->toDateString(),
            'status' => 'active',
        ]);

        // 13. Teaching Sessions
        $session1 = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'schedule_id' => $schedule1->id,
            'class_id' => $classMatematika->id,
            'scheduled_tutor_id' => $tutorAhmad->id,
            'actual_tutor_id' => $tutorAhmad->id,
            'session_date' => now()->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang M-1',
            'status' => 'completed',
            'material' => 'Kalkulus: Turunan Fungsi Aljabar',
            'notes' => 'Semua materi tersampaikan dengan baik dan latihan soal tuntas.',
        ]);

        $session2 = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'schedule_id' => $schedule2->id,
            'class_id' => $classFisika->id,
            'scheduled_tutor_id' => $tutorDewi->id,
            'actual_tutor_id' => $tutorBambang->id,
            'session_date' => now()->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang M-2',
            'status' => 'completed',
            'material' => 'Kinematika Gerak Lurus Beraturan dan Berubah Beraturan',
            'notes' => 'Tutor pengganti (Bambang) hadir tepat waktu.',
        ]);

        // 14. Attendances
        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'teaching_session_id' => $session1->id,
            'student_id' => $student1->id,
            'status' => 'hadir',
            'note' => null,
            'recorded_at' => now(),
            'recorded_by' => $tutorAhmad->id,
        ]);

        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'teaching_session_id' => $session1->id,
            'student_id' => $student2->id,
            'status' => 'hadir',
            'note' => null,
            'recorded_at' => now(),
            'recorded_by' => $tutorAhmad->id,
        ]);

        TutorAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'teaching_session_id' => $session1->id,
            'tutor_id' => $tutorAhmad->id,
            'status' => 'present',
            'recorded_at' => now(),
            'source' => 'student_attendance_submission',
        ]);

        // 15. Payments across branches
        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'student_id' => $student1->id,
            'enrollment_id' => $enrollment1->id,
            'period' => now()->format('Y-m'),
            'amount' => 750000.00,
            'due_date' => now()->endOfMonth()->toDateString(),
            'paid_at' => now(),
            'status' => 'lunas',
            'notes' => 'Transfer via BCA',
            'recorded_by' => $adminMalang->id,
        ]);

        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'student_id' => $student2->id,
            'enrollment_id' => $enrollment2->id,
            'period' => now()->format('Y-m'),
            'amount' => 750000.00,
            'due_date' => now()->endOfMonth()->toDateString(),
            'paid_at' => null,
            'status' => 'belum_bayar',
            'notes' => 'Menunggu konfirmasi pembayaran orang tua',
            'recorded_by' => $adminMalang->id,
        ]);

        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMakassar->id,
            'student_id' => $student3->id,
            'enrollment_id' => $enrollment3->id,
            'period' => now()->format('Y-m'),
            'amount' => 600000.00,
            'due_date' => now()->endOfMonth()->toDateString(),
            'paid_at' => now()->subDay(),
            'status' => 'lunas',
            'notes' => 'Transfer Bank Mandiri',
            'recorded_by' => $adminMakassar->id,
        ]);

        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchSemarang->id,
            'student_id' => $student4->id,
            'enrollment_id' => $enrollment4->id,
            'period' => now()->format('Y-m'),
            'amount' => 650000.00,
            'due_date' => now()->endOfMonth()->toDateString(),
            'paid_at' => null,
            'status' => 'terlambat',
            'notes' => 'Reminder WhatsApp terkirim',
            'recorded_by' => $adminSemarang->id,
        ]);

        // 16. Honor Calculations
        HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchMalang->id,
            'tutor_id' => $tutorAhmad->id,
            'honor_scheme_id' => $honorSchemeReguler->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'method' => 'per_session',
            'base_amount' => 1200000.00,
            'adjustment_amount' => 100000.00,
            'final_amount' => 1300000.00,
            'status' => 'final',
            'adjustment_reason' => 'Bonus apresiasi kehadiran penuh',
            'finalized_at' => now(),
            'paid_at' => null,
            'calculated_by' => $adminMalang->id,
            'finalized_by' => $owner->id,
        ]);

        // 17. Audit Logs
        AuditLog::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'actor_user_id' => $adminMalang->id,
            'action' => 'payment.record',
            'entity_type' => Payment::class,
            'entity_id' => Str::uuid(),
            'old_values' => null,
            'new_values' => ['status' => 'lunas', 'amount' => 750000.00],
            'metadata' => ['method' => 'manual_transfer'],
        ]);
    }
}
