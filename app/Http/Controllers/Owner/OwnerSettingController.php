<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdatePasswordRequest;
use App\Http\Requests\Owner\UpdateProfileRequest;
use App\Http\Requests\Owner\UpdateTenantSettingsRequest;
use App\Models\Branch;
use App\Models\Student;
use App\Models\TenantSetting;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OwnerSettingController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;
        $user = $request->user();

        $branchCount = Branch::where('tenant_id', $tenant->id)->count();
        $studentCount = Student::where('tenant_id', $tenant->id)->count();
        $tutorCount = User::where('tenant_id', $tenant->id)->where('role', 'tutor')->count();

        $generalSetting = TenantSetting::where('tenant_id', $tenant->id)
            ->where('key', 'general_settings')
            ->first();

        $preferences = $generalSetting?->value ?? [
            'notifications_enabled' => true,
            'two_factor_enabled' => false,
            'language' => 'id',
        ];

        return view('owner.settings.index', [
            'tenant' => $tenant,
            'user' => $user,
            'stats' => [
                'branchCount' => $branchCount,
                'studentCount' => $studentCount,
                'tutorCount' => $tutorCount,
            ],
            'preferences' => $preferences,
        ]);
    }

    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $user->update($validated);

        return redirect()->route('owner.settings')->with('success', 'Profil Owner berhasil diperbarui.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('owner.settings')->with('success', 'Kata sandi akun Anda berhasil diubah.');
    }

    public function updateTenant(UpdateTenantSettingsRequest $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;
        $validated = $request->validated();

        $tenant->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? $tenant->email,
            'phone' => $validated['phone'] ?? $tenant->phone,
            'address' => $validated['address'] ?? $tenant->address,
            'description' => $validated['description'] ?? $tenant->description,
        ]);

        $generalSetting = TenantSetting::firstOrNew([
            'tenant_id' => $tenant->id,
            'key' => 'general_settings',
        ]);

        $currentValues = $generalSetting->value ?? [];
        $generalSetting->value = array_merge($currentValues, [
            'notifications_enabled' => $request->boolean('notifications_enabled', true),
            'language' => $validated['language'] ?? 'id',
        ]);
        $generalSetting->save();

        return redirect()->route('owner.settings')->with('success', 'Pengaturan informasi bimbel & preferensi berhasil disimpan.');
    }
}
