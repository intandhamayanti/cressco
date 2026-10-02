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

        // 2. Tenant
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'Bimbel Cressco Academy',
            'slug' => 'cressco-academy',
            'status' => 'active',
            'logo' => null,
            'description' => 'Bimbingan Belajar Berkualitas SD, SMP, dan SMA',
            'address' => 'Jl. Pemuda No. 45, Surabaya',
            'phone' => '081234567890',
            'email' => 'contact@cressco-academy.com',
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

        // 3. Branches
        $branchPusat = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Cabang Surabaya Pusat',
            'code' => 'SBY-01',
            'address' => 'Jl. Basuki Rahmat No. 12, Surabaya',
            'phone' => '081234567801',
            'status' => 'active',
        ]);

        $branchBarat = Branch::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Cabang Surabaya Barat',
            'code' => 'SBY-02',
            'address' => 'Jl. HR Muhammad No. 88, Surabaya',
            'phone' => '081234567802',
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

        $adminPusat = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Siti Rahmawati',
            'email' => 'admin.pusat@cressco.test',
            'email_verified_at' => now(),
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $adminBarat = User::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Rizky Ramadhan',
            'email' => 'admin.barat@cressco.test',
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

        // 5. BranchUser explicit assignments for Admins
        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'user_id' => $adminPusat->id,
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchBarat->id,
            'user_id' => $adminPusat->id,
        ]);

        BranchUser::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchBarat->id,
            'user_id' => $adminBarat->id,
        ]);

        // 6. Students
        $student1 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'name' => 'Andi Pratama',
            'date_of_birth' => '2008-05-14',
            'gender' => 'Laki-laki',
            'phone' => '081311223344',
            'address' => 'Jl. Gubeng Kertajaya No. 15, Surabaya',
            'parent_name' => 'Joko Pratama',
            'parent_phone' => '081299001122',
            'notes' => 'Target masuk ITB Teknik Informatika',
            'joined_at' => '2026-01-10',
            'status' => 'active',
        ]);

        $student2 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'name' => 'Nadia Amanda',
            'date_of_birth' => '2008-08-20',
            'gender' => 'Perempuan',
            'phone' => '081322334455',
            'address' => 'Jl. Dharmawangsa No. 22, Surabaya',
            'parent_name' => 'Hendra Amanda',
            'parent_phone' => '081299003344',
            'notes' => 'Target masuk Kedokteran UNAIR',
            'joined_at' => '2026-01-12',
            'status' => 'active',
        ]);

        $student3 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'name' => 'Dimas Setiawan',
            'date_of_birth' => '2008-11-03',
            'gender' => 'Laki-laki',
            'phone' => '081333445566',
            'address' => 'Jl. Manyar Sabrangan No. 5, Surabaya',
            'parent_name' => 'Agus Setiawan',
            'parent_phone' => '081299005566',
            'notes' => 'Perlu bimbingan ekstra Matematika',
            'joined_at' => '2026-01-15',
            'status' => 'active',
        ]);

        $student4 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchBarat->id,
            'name' => 'Rina Marlina',
            'date_of_birth' => '2011-03-25',
            'gender' => 'Perempuan',
            'phone' => '081344556677',
            'address' => 'Jl. Darmo Permai No. 8, Surabaya',
            'parent_name' => 'Bambang Marlina',
            'parent_phone' => '081299007788',
            'notes' => 'Siswa SMP berprestasi',
            'joined_at' => '2026-01-18',
            'status' => 'active',
        ]);

        $student5 = Student::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchBarat->id,
            'name' => 'Fajar Nugraha',
            'date_of_birth' => '2011-07-19',
            'gender' => 'Laki-laki',
            'phone' => '081355667788',
            'address' => 'Jl. Bukit Darmo Golf No. 10, Surabaya',
            'parent_name' => 'Dedi Nugraha',
            'parent_phone' => '081299009900',
            'notes' => 'Persiapan ujian sekolah',
            'joined_at' => '2026-01-20',
            'status' => 'active',
        ]);

        // 7. Classes
        $classMatematika = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'name' => '12 IPA - Matematika Intensif',
            'subject' => 'Matematika',
            'level' => '12 SMA',
            'capacity' => 15,
            'status' => 'active',
        ]);

        $classFisika = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'name' => '12 IPA - Fisika UTBK',
            'subject' => 'Fisika',
            'level' => '12 SMA',
            'capacity' => 15,
            'status' => 'active',
        ]);

        $classInggris = Classes::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchBarat->id,
            'name' => '9 SMP - Bahasa Inggris Reguler',
            'subject' => 'Bahasa Inggris',
            'level' => '9 SMP',
            'capacity' => 20,
            'status' => 'active',
        ]);

        // 8. Enrollments
        $enrollment1 = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'student_id' => $student1->id,
            'class_id' => $classMatematika->id,
            'started_at' => '2026-01-10',
            'status' => 'active',
        ]);

        $enrollment2 = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'student_id' => $student2->id,
            'class_id' => $classMatematika->id,
            'started_at' => '2026-01-12',
            'status' => 'active',
        ]);

        $enrollment3 = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'student_id' => $student3->id,
            'class_id' => $classMatematika->id,
            'started_at' => '2026-01-15',
            'status' => 'active',
        ]);

        $enrollment4 = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'student_id' => $student1->id,
            'class_id' => $classFisika->id,
            'started_at' => '2026-01-10',
            'status' => 'active',
        ]);

        $enrollment5 = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'student_id' => $student2->id,
            'class_id' => $classFisika->id,
            'started_at' => '2026-01-12',
            'status' => 'active',
        ]);

        $enrollment6 = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchBarat->id,
            'student_id' => $student4->id,
            'class_id' => $classInggris->id,
            'started_at' => '2026-01-18',
            'status' => 'active',
        ]);

        $enrollment7 = Enrollment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchBarat->id,
            'student_id' => $student5->id,
            'class_id' => $classInggris->id,
            'started_at' => '2026-01-20',
            'status' => 'active',
        ]);

        // 9. Tutor Assignments
        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'tutor_id' => $tutorAhmad->id,
            'class_id' => $classMatematika->id,
            'started_at' => '2026-01-01',
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'tutor_id' => $tutorDewi->id,
            'class_id' => $classFisika->id,
            'started_at' => '2026-01-01',
            'status' => 'active',
        ]);

        TutorAssignment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchBarat->id,
            'tutor_id' => $tutorBambang->id,
            'class_id' => $classInggris->id,
            'started_at' => '2026-01-01',
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
            'branch_id' => $branchPusat->id,
            'class_id' => $classMatematika->id,
            'scheduled_tutor_id' => $tutorAhmad->id,
            'day_of_week' => 1, // Monday
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang A1',
            'starts_on' => '2026-01-01',
            'status' => 'active',
        ]);

        $schedule2 = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'class_id' => $classFisika->id,
            'scheduled_tutor_id' => $tutorDewi->id,
            'day_of_week' => 3, // Wednesday
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang A2',
            'starts_on' => '2026-01-01',
            'status' => 'active',
        ]);

        $schedule3 = Schedule::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchBarat->id,
            'class_id' => $classInggris->id,
            'scheduled_tutor_id' => $tutorBambang->id,
            'day_of_week' => 5, // Friday
            'start_time' => '15:30:00',
            'end_time' => '17:00:00',
            'room' => 'Ruang B1',
            'starts_on' => '2026-01-01',
            'status' => 'active',
        ]);

        // 13. Teaching Sessions
        $session1 = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'schedule_id' => $schedule1->id,
            'class_id' => $classMatematika->id,
            'scheduled_tutor_id' => $tutorAhmad->id,
            'actual_tutor_id' => $tutorAhmad->id,
            'session_date' => '2026-02-02',
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang A1',
            'status' => 'completed',
            'material' => 'Kalkulus: Turunan Fungsi Aljabar',
            'notes' => 'Semua materi tersampaikan dengan baik dan latihan soal tuntas.',
        ]);

        $session2 = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'schedule_id' => $schedule2->id,
            'class_id' => $classFisika->id,
            'scheduled_tutor_id' => $tutorDewi->id,
            'actual_tutor_id' => $tutorBambang->id,
            'session_date' => '2026-02-04',
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang A2',
            'status' => 'completed',
            'material' => 'Kinematika Gerak Lurus Beraturan dan Berubah Beraturan',
            'notes' => 'Tutor pengganti (Bambang) hadir tepat waktu menggantikan Dewi.',
        ]);

        $session3 = TeachingSession::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'schedule_id' => $schedule1->id,
            'class_id' => $classMatematika->id,
            'scheduled_tutor_id' => $tutorAhmad->id,
            'actual_tutor_id' => null,
            'session_date' => '2026-02-09',
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
            'room' => 'Ruang A1',
            'status' => 'scheduled',
            'material' => 'Aplikasi Turunan: Nilai Maksimum & Minimum',
            'notes' => null,
        ]);

        // 14. Tutor Replacement Record (for Session 2)
        $tutorReplacement = TutorReplacement::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'teaching_session_id' => $session2->id,
            'scheduled_tutor_id' => $tutorDewi->id,
            'previous_actual_tutor_id' => null,
            'replacement_tutor_id' => $tutorBambang->id,
            'reason' => 'Tutor Dewi sedang berhalangan hadir karena urusan keluarga.',
            'changed_by' => $adminPusat->id,
            'changed_at' => Carbon::parse('2026-02-03 14:00:00'),
        ]);

        // 15. Attendances
        // Session 1 attendances
        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'teaching_session_id' => $session1->id,
            'student_id' => $student1->id,
            'status' => 'hadir',
            'note' => null,
            'recorded_at' => Carbon::parse('2026-02-02 17:35:00'),
            'recorded_by' => $tutorAhmad->id,
        ]);

        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'teaching_session_id' => $session1->id,
            'student_id' => $student2->id,
            'status' => 'hadir',
            'note' => null,
            'recorded_at' => Carbon::parse('2026-02-02 17:35:00'),
            'recorded_by' => $tutorAhmad->id,
        ]);

        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'teaching_session_id' => $session1->id,
            'student_id' => $student3->id,
            'status' => 'izin',
            'note' => 'Izin sakit flu',
            'recorded_at' => Carbon::parse('2026-02-02 17:35:00'),
            'recorded_by' => $tutorAhmad->id,
        ]);

        TutorAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'teaching_session_id' => $session1->id,
            'tutor_id' => $tutorAhmad->id,
            'status' => 'present',
            'recorded_at' => Carbon::parse('2026-02-02 17:35:00'),
            'source' => 'student_attendance_submission',
        ]);

        // Session 2 attendances
        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'teaching_session_id' => $session2->id,
            'student_id' => $student1->id,
            'status' => 'hadir',
            'note' => null,
            'recorded_at' => Carbon::parse('2026-02-04 17:35:00'),
            'recorded_by' => $tutorBambang->id,
        ]);

        StudentAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'teaching_session_id' => $session2->id,
            'student_id' => $student2->id,
            'status' => 'hadir',
            'note' => null,
            'recorded_at' => Carbon::parse('2026-02-04 17:35:00'),
            'recorded_by' => $tutorBambang->id,
        ]);

        TutorAttendance::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'teaching_session_id' => $session2->id,
            'tutor_id' => $tutorBambang->id,
            'status' => 'present',
            'recorded_at' => Carbon::parse('2026-02-04 17:35:00'),
            'source' => 'student_attendance_submission',
        ]);

        // 16. Assessments and Results
        $assessmentQuiz = Assessment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'class_id' => $classMatematika->id,
            'name' => 'Quiz 1 - Turunan Aljabar',
            'type' => 'quiz',
            'material' => 'Turunan dasar dan aturan rantai',
            'assessment_date' => '2026-02-02',
            'max_score' => 100.00,
            'notes' => '10 soal pilihan ganda dan 2 soal essay',
            'created_by' => $tutorAhmad->id,
        ]);

        AssessmentResult::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'assessment_id' => $assessmentQuiz->id,
            'student_id' => $student1->id,
            'score' => 95.00,
            'notes' => 'Sangat memahami konsep aturan rantai',
        ]);

        AssessmentResult::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'assessment_id' => $assessmentQuiz->id,
            'student_id' => $student2->id,
            'score' => 85.00,
            'notes' => 'Perlu latihan lebih teliti pada perhitungan',
        ]);

        AssessmentResult::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'assessment_id' => $assessmentQuiz->id,
            'student_id' => $student3->id,
            'score' => 0.00,
            'notes' => 'Susulan diperlukan (izin sakit)',
        ]);

        // 17. Payments
        $payment1 = Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'student_id' => $student1->id,
            'enrollment_id' => $enrollment1->id,
            'period' => '2026-02',
            'amount' => 500000.00,
            'due_date' => '2026-02-10',
            'paid_at' => Carbon::parse('2026-02-05 10:00:00'),
            'status' => 'lunas',
            'notes' => 'Transfer via BCA',
            'recorded_by' => $adminPusat->id,
        ]);

        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'student_id' => $student2->id,
            'enrollment_id' => $enrollment2->id,
            'period' => '2026-02',
            'amount' => 500000.00,
            'due_date' => '2026-02-10',
            'paid_at' => null,
            'status' => 'belum_bayar',
            'notes' => 'Menunggu konfirmasi orang tua',
            'recorded_by' => $adminPusat->id,
        ]);

        Payment::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchBarat->id,
            'student_id' => $student4->id,
            'enrollment_id' => $enrollment6->id,
            'period' => '2026-02',
            'amount' => 450000.00,
            'due_date' => '2026-02-10',
            'paid_at' => Carbon::parse('2026-02-08 11:30:00'),
            'status' => 'menunggu_verifikasi',
            'notes' => 'Bukti transfer diunggah lewat admin',
            'recorded_by' => $adminBarat->id,
        ]);

        // 18. Honor Calculation
        HonorCalculation::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'branch_id' => $branchPusat->id,
            'tutor_id' => $tutorAhmad->id,
            'honor_scheme_id' => $honorSchemeReguler->id,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'method' => 'per_session',
            'base_amount' => 600000.00,
            'adjustment_amount' => 50000.00,
            'final_amount' => 650000.00,
            'status' => 'final',
            'adjustment_reason' => 'Bonus apresiasi kedisiplinan dan feedback siswa sangat baik',
            'finalized_at' => Carbon::parse('2026-02-28 20:00:00'),
            'paid_at' => null,
            'calculated_by' => $adminPusat->id,
            'finalized_by' => $owner->id,
        ]);

        // 19. Audit Logs
        AuditLog::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'actor_user_id' => $adminPusat->id,
            'action' => 'payment.record',
            'entity_type' => Payment::class,
            'entity_id' => $payment1->id,
            'old_values' => null,
            'new_values' => ['status' => 'lunas', 'amount' => 500000.00],
            'metadata' => ['method' => 'manual_transfer'],
        ]);

        AuditLog::create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'actor_user_id' => $adminPusat->id,
            'action' => 'tutor_replacement.create',
            'entity_type' => TutorReplacement::class,
            'entity_id' => $tutorReplacement->id,
            'old_values' => null,
            'new_values' => ['scheduled_tutor_id' => $tutorDewi->id, 'replacement_tutor_id' => $tutorBambang->id],
            'metadata' => ['session_id' => $session2->id],
        ]);
    }
}
