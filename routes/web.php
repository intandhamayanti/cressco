<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::get('design-system/{section?}', function (?string $section = null) {
    $validSections = ['color', 'typography', 'text-field', 'button', 'navigation', 'chart-card', 'other'];
    $param = request()->query('page') ?? request()->query('tab') ?? request()->query('section');
    $activeSection = $section ?? $param ?? 'button';

    if (! in_array($activeSection, $validSections)) {
        $activeSection = 'button';
    }

    return view('design-system', ['activeSection' => $activeSection]);
})->name('design-system');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Route Protection Foundation (for testing & architectural scaffolding)
Route::middleware(['auth', 'tenant', 'role:owner'])->prefix('owner')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'owner_ok']));
});

Route::middleware(['auth', 'tenant', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'admin_ok']));
});

Route::middleware(['auth', 'tenant', 'role:tutor'])->prefix('tutor')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'tutor_ok']));
});

Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'super_admin_ok']));
});

Route::middleware(['auth', 'tenant', 'role:owner,admin'])->prefix('tenant-ops')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'ops_ok']));
});

require __DIR__.'/auth.php';
