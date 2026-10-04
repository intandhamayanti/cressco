<?php

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminClassController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminHonorController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminTutorController;
use App\Http\Controllers\Owner\OwnerBranchController;
use App\Http\Controllers\Owner\OwnerClassController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Owner\OwnerHonorController;
use App\Http\Controllers\Owner\OwnerPaymentController;
use App\Http\Controllers\Owner\OwnerReportController;
use App\Http\Controllers\Owner\OwnerSettingController;
use App\Http\Controllers\Owner\OwnerStudentController;
use App\Http\Controllers\Owner\OwnerTutorController;
use App\Http\Controllers\Owner\OwnerUserController;
use App\Http\Controllers\Tutor\TutorAssessmentController;
use App\Http\Controllers\Tutor\TutorAttendanceController;
use App\Http\Controllers\Tutor\TutorClassController;
use App\Http\Controllers\Tutor\TutorDashboardController;
use App\Http\Controllers\Tutor\TutorProfileController;
use App\Http\Controllers\Tutor\TutorScheduleController;
use App\Http\Controllers\Tutor\TutorSessionController;
use App\Http\Controllers\Tutor\TutorTeachingHistoryController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::get('design-system/{section?}', function (?string $section = null) {
    $validSections = ['color', 'typography', 'text-field', 'button', 'navigation', 'card-chart', 'chart-card', 'other'];
    $param = request()->query('page') ?? request()->query('tab') ?? request()->query('section');
    $activeSection = $section ?? $param ?? 'button';

    if ($activeSection === 'chart-card') {
        $activeSection = 'card-chart';
    }

    if (! in_array($activeSection, $validSections)) {
        $activeSection = 'button';
    }

    return view('design-system', ['activeSection' => $activeSection]);
})->name('design-system');

Route::get('dashboard', function () {
    $user = auth()->user();
    if ($user && $user->isOwner()) {
        return redirect()->route('owner.dashboard');
    }
    if ($user && $user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    if ($user && $user->isTutor()) {
        return redirect()->route('tutor.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('profile', function () {
    $user = auth()->user();
    if ($user && $user->isOwner()) {
        return redirect()->route('owner.settings');
    }

    return view('profile');
})->middleware(['auth'])->name('profile');

// Owner Portal Routes
Route::middleware(['auth', 'tenant', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'owner_ok']));
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');

    // Management Cabang
    Route::get('/branches', [OwnerBranchController::class, 'index'])->name('branches.index');
    Route::post('/branches', [OwnerBranchController::class, 'store'])->name('branches.store');
    Route::get('/branches/{branch}', [OwnerBranchController::class, 'show'])->name('branches.show');
    Route::put('/branches/{branch}', [OwnerBranchController::class, 'update'])->name('branches.update');
    Route::patch('/branches/{branch}/toggle-status', [OwnerBranchController::class, 'toggleStatus'])->name('branches.toggle-status');

    // Management Users
    Route::get('/users', [OwnerUserController::class, 'index'])->name('users.index');
    Route::post('/users', [OwnerUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [OwnerUserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [OwnerUserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle-status', [OwnerUserController::class, 'toggleStatus'])->name('users.toggle-status');

    // Management Siswa
    Route::get('/students', [OwnerStudentController::class, 'index'])->name('students.index');
    Route::post('/students', [OwnerStudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [OwnerStudentController::class, 'show'])->name('students.show');
    Route::put('/students/{student}', [OwnerStudentController::class, 'update'])->name('students.update');
    Route::patch('/students/{student}/toggle-status', [OwnerStudentController::class, 'toggleStatus'])->name('students.toggle-status');

    // Management Kelas & Jadwal
    Route::get('/classes', [OwnerClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [OwnerClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{class}', [OwnerClassController::class, 'show'])->name('classes.show');
    Route::put('/classes/{class}', [OwnerClassController::class, 'update'])->name('classes.update');
    Route::patch('/classes/{class}/toggle-status', [OwnerClassController::class, 'toggleStatus'])->name('classes.toggle-status');
    Route::post('/classes/{class}/tutors', [OwnerClassController::class, 'assignTutor'])->name('classes.assign-tutor');
    Route::patch('/classes/{class}/tutors/{assignment}/toggle-status', [OwnerClassController::class, 'toggleTutorAssignment'])->name('classes.tutors.toggle-status');
    Route::post('/classes/{class}/schedules', [OwnerClassController::class, 'storeSchedule'])->name('classes.schedules.store');
    Route::put('/classes/{class}/schedules/{schedule}', [OwnerClassController::class, 'updateSchedule'])->name('classes.schedules.update');
    Route::patch('/classes/{class}/schedules/{schedule}/toggle-status', [OwnerClassController::class, 'toggleScheduleStatus'])->name('classes.schedules.toggle-status');
    Route::delete('/classes/{class}/schedules/{schedule}', [OwnerClassController::class, 'destroySchedule'])->name('classes.schedules.destroy');

    // Management Tutor
    Route::get('/tutors', [OwnerTutorController::class, 'index'])->name('tutors.index');
    Route::post('/tutors', [OwnerTutorController::class, 'store'])->name('tutors.store');
    Route::get('/tutors/{tutor}', [OwnerTutorController::class, 'show'])->name('tutors.show');
    Route::put('/tutors/{tutor}', [OwnerTutorController::class, 'update'])->name('tutors.update');
    Route::patch('/tutors/{tutor}/toggle-status', [OwnerTutorController::class, 'toggleStatus'])->name('tutors.toggle-status');
    Route::post('/tutors/{tutor}/classes', [OwnerTutorController::class, 'assignClass'])->name('tutors.assign-class');
    Route::patch('/tutors/{tutor}/classes/{assignment}/toggle-status', [OwnerTutorController::class, 'toggleClassAssignment'])->name('tutors.classes.toggle-status');
    Route::post('/tutors/{tutor}/honor-scheme', [OwnerTutorController::class, 'assignHonorScheme'])->name('tutors.assign-honor-scheme');

    // Honor Tutor (Oversight & Configuration)
    Route::get('/honors', [OwnerHonorController::class, 'index'])->name('honors.index');
    Route::post('/honors/calculate', [OwnerHonorController::class, 'calculate'])->name('honors.calculate');
    Route::get('/honors/{calculation}', [OwnerHonorController::class, 'show'])->name('honors.show');
    Route::patch('/honors/{calculation}/finalize', [OwnerHonorController::class, 'finalize'])->name('honors.finalize');
    Route::patch('/honors/{calculation}/mark-paid', [OwnerHonorController::class, 'markPaid'])->name('honors.mark-paid');
    Route::post('/honors/schemes', [OwnerHonorController::class, 'storeScheme'])->name('honors.schemes.store');
    Route::put('/honors/schemes/{scheme}', [OwnerHonorController::class, 'updateScheme'])->name('honors.schemes.update');
    Route::patch('/honors/schemes/{scheme}/toggle-status', [OwnerHonorController::class, 'toggleSchemeStatus'])->name('honors.schemes.toggle-status');
    Route::patch('/honors/schemes/{scheme}/set-default', [OwnerHonorController::class, 'setDefaultScheme'])->name('honors.schemes.set-default');

    // Pembayaran & Invoice
    Route::get('/payments', [OwnerPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [OwnerPaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [OwnerPaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/reminder', [OwnerPaymentController::class, 'reminder'])->name('payments.reminder');
    Route::put('/payments/{payment}', [OwnerPaymentController::class, 'update'])->name('payments.update');
    Route::patch('/payments/{payment}/verify', [OwnerPaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/partial', [OwnerPaymentController::class, 'recordPartialPayment'])->name('payments.partial');
    Route::post('/payments/{payment}/proof', [OwnerPaymentController::class, 'submitProof'])->name('payments.submit-proof');
    Route::delete('/payments/{payment}', [OwnerPaymentController::class, 'destroy'])->name('payments.destroy');

    // Laporan (Financial & Operational Reports)
    Route::get('/reports', [OwnerReportController::class, 'index'])->name('reports.index');

    // Pengaturan & Profil Owner
    Route::get('/settings', [OwnerSettingController::class, 'index'])->name('settings');
    Route::put('/settings/profile', [OwnerSettingController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password', [OwnerSettingController::class, 'updatePassword'])->name('settings.password');
    Route::put('/settings/tenant', [OwnerSettingController::class, 'updateTenant'])->name('settings.tenant');
});

// Admin Portal Routes
Route::middleware(['auth', 'tenant', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'admin_ok']));
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Management Siswa
    Route::get('/students', [AdminStudentController::class, 'index'])->name('students.index');
    Route::post('/students', [AdminStudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [AdminStudentController::class, 'show'])->name('students.show');
    Route::put('/students/{student}', [AdminStudentController::class, 'update'])->name('students.update');
    Route::patch('/students/{student}/toggle-status', [AdminStudentController::class, 'toggleStatus'])->name('students.toggle-status');

    // Management Kelas & Jadwal
    Route::get('/classes', [AdminClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [AdminClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{class}', [AdminClassController::class, 'show'])->name('classes.show');
    Route::put('/classes/{class}', [AdminClassController::class, 'update'])->name('classes.update');
    Route::patch('/classes/{class}/toggle-status', [AdminClassController::class, 'toggleStatus'])->name('classes.toggle-status');
    Route::post('/classes/{class}/tutors', [AdminClassController::class, 'assignTutor'])->name('classes.assign-tutor');
    Route::patch('/classes/{class}/tutors/{assignment}/toggle-status', [AdminClassController::class, 'toggleTutorAssignment'])->name('classes.tutors.toggle-status');
    Route::post('/classes/{class}/schedules', [AdminClassController::class, 'storeSchedule'])->name('classes.schedules.store');
    Route::put('/classes/{class}/schedules/{schedule}', [AdminClassController::class, 'updateSchedule'])->name('classes.schedules.update');
    Route::patch('/classes/{class}/schedules/{schedule}/toggle-status', [AdminClassController::class, 'toggleScheduleStatus'])->name('classes.schedules.toggle-status');
    Route::delete('/classes/{class}/schedules/{schedule}', [AdminClassController::class, 'destroySchedule'])->name('classes.schedules.destroy');

    // Management Tutor & Operasional
    Route::get('/tutors', [AdminTutorController::class, 'index'])->name('tutors.index');
    Route::post('/tutors', [AdminTutorController::class, 'store'])->name('tutors.store');
    Route::get('/tutors/{tutor}', [AdminTutorController::class, 'show'])->name('tutors.show');
    Route::put('/tutors/{tutor}', [AdminTutorController::class, 'update'])->name('tutors.update');
    Route::patch('/tutors/{tutor}/toggle-status', [AdminTutorController::class, 'toggleStatus'])->name('tutors.toggle-status');
    Route::post('/tutors/{tutor}/classes', [AdminTutorController::class, 'assignClass'])->name('tutors.assign-class');
    Route::patch('/tutors/{tutor}/classes/{assignment}/toggle-status', [AdminTutorController::class, 'toggleClassAssignment'])->name('tutors.classes.toggle-status');

    // Presensi & Absensi Siswa
    Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/sessions/{session}', [AdminAttendanceController::class, 'showSession'])->name('attendances.sessions.show');
    Route::post('/attendances/sessions/{session}/replace-tutor', [AdminAttendanceController::class, 'replaceTutor'])->name('attendances.sessions.replace-tutor');
    Route::put('/attendances/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendances.update');

    // Pembayaran & Reminder Orang Tua
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments', [AdminPaymentController::class, 'store'])->name('payments.store');
    Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/{payment}/reminder', [AdminPaymentController::class, 'reminder'])->name('payments.reminder');
    Route::put('/payments/{payment}', [AdminPaymentController::class, 'update'])->name('payments.update');
    Route::patch('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/partial', [AdminPaymentController::class, 'recordPartialPayment'])->name('payments.partial');
    Route::post('/payments/{payment}/proof', [AdminPaymentController::class, 'submitProof'])->name('payments.submit-proof');
    Route::delete('/payments/{payment}', [AdminPaymentController::class, 'destroy'])->name('payments.destroy');

    // Honor Tutor (Operasional & Actual Teaching Sessions)
    Route::get('/honors', [AdminHonorController::class, 'index'])->name('honors.index');
    Route::post('/honors/calculate', [AdminHonorController::class, 'calculate'])->name('honors.calculate');
    Route::get('/honors/{calculation}', [AdminHonorController::class, 'show'])->name('honors.show');
    Route::patch('/honors/{calculation}/finalize', [AdminHonorController::class, 'finalize'])->name('honors.finalize');
    Route::patch('/honors/{calculation}/mark-paid', [AdminHonorController::class, 'markPaid'])->name('honors.mark-paid');

    // Laporan Operasional Cabang
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');

    // Profil Admin & Pengaturan Akun
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::middleware(['auth', 'tenant', 'role:tutor'])->prefix('tutor')->name('tutor.')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'tutor_ok']));
    Route::get('/dashboard', [TutorDashboardController::class, 'index'])->name('dashboard');

    // Jadwal Mengajar
    Route::get('/schedules', [TutorScheduleController::class, 'index'])->name('schedules.index');

    // Sesi Mengajar & Materi
    Route::get('/sessions', [TutorSessionController::class, 'index'])->name('sessions.index');
    Route::get('/sessions/{session}', [TutorSessionController::class, 'show'])->name('sessions.show');
    Route::put('/sessions/{session}', [TutorSessionController::class, 'update'])->name('sessions.update');

    // Presensi & Absensi Siswa
    Route::post('/sessions/{session}/attendances', [TutorAttendanceController::class, 'store'])->name('sessions.attendances.store');
    Route::put('/attendances/{attendance}', [TutorAttendanceController::class, 'update'])->name('attendances.update');

    // Penilaian Siswa
    Route::get('/assessments', [TutorAssessmentController::class, 'index'])->name('assessments.index');
    Route::get('/assessments/create', [TutorAssessmentController::class, 'create'])->name('assessments.create');
    Route::post('/assessments', [TutorAssessmentController::class, 'store'])->name('assessments.store');
    Route::get('/assessments/{assessment}', [TutorAssessmentController::class, 'show'])->name('assessments.show');
    Route::get('/assessments/{assessment}/edit', [TutorAssessmentController::class, 'edit'])->name('assessments.edit');
    Route::put('/assessments/{assessment}', [TutorAssessmentController::class, 'update'])->name('assessments.update');
    Route::post('/assessments/{assessment}/results', [TutorAssessmentController::class, 'storeResults'])->name('assessments.results.store');
    Route::delete('/assessments/{assessment}', [TutorAssessmentController::class, 'destroy'])->name('assessments.destroy');

    // Riwayat Mengajar
    Route::get('/history', [TutorTeachingHistoryController::class, 'index'])->name('history.index');

    // Kelas yang Diampu
    Route::get('/classes', [TutorClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/{class}', [TutorClassController::class, 'show'])->name('classes.show');

    // Profil Tutor & Pengaturan Akun
    Route::get('/profile', [TutorProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [TutorProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [TutorProfileController::class, 'updatePassword'])->name('profile.password');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'super_admin_ok']));
});

Route::middleware(['auth', 'tenant', 'role:owner,admin'])->prefix('tenant-ops')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'ops_ok']));
});

require __DIR__.'/auth.php';
