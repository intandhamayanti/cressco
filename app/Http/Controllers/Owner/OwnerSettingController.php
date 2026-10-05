<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdatePasswordRequest;
use App\Http\Requests\Owner\UpdateProfileRequest;
use App\Http\Requests\Owner\UpdateTenantSettingsRequest;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\HonorAssignment;
use App\Models\HonorScheme;
use App\Models\Student;
use App\Models\TenantSetting;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OwnerSettingController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        $branchCount = Branch::where('tenant_id', $tenant->id)->count();
        $studentCount = Student::where('tenant_id', $tenant->id)->count();
        $tutorCount = User::where('tenant_id', $tenant->id)->where('role', 'tutor')->count();
        $classCount = Classes::where('tenant_id', $tenant->id)->count();

        $generalSetting = TenantSetting::where('tenant_id', $tenant->id)
            ->where('key', 'general_settings')
            ->first();

        $preferences = $generalSetting?->value ?? [
            'notifications_enabled' => true,
            'two_factor_enabled' => false,
            'language' => 'id',
        ];

        // Default Bimbel Honor Scheme
        $defaultHonorAssignment = HonorAssignment::where('tenant_id', $tenant->id)
            ->where('assignment_type', 'default')
            ->with('honorScheme')
            ->first();
        $defaultHonorScheme = $defaultHonorAssignment?->honorScheme;

        return view('owner.settings.index', [
            'tenant' => $tenant,
            'user' => $request->user(),
            'stats' => [
                'branchCount' => $branchCount,
                'studentCount' => $studentCount,
                'tutorCount' => $tutorCount,
                'classCount' => $classCount,
            ],
            'preferences' => $preferences,
            'defaultHonorScheme' => $defaultHonorScheme,
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

        return redirect()->route('owner.settings')->with('success', 'Profil owner berhasil diperbarui.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('owner.settings')->with('success', 'Kata sandi berhasil diperbarui.');
    }

    public function updateTenant(UpdateTenantSettingsRequest $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

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

    public function updateDefaultHonor(Request $request): RedirectResponse
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;

        if (! $tenant) {
            abort(403, 'Tenant context not found.');
        }

        $validated = $request->validate([
            'method' => ['required', 'string', 'in:per_session,per_student,fixed_monthly'],
            'rate' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
        ]);

        $method = $validated['method'];
        $rate = (float) $validated['rate'];
        $effectiveFrom = $validated['effective_from'];

        $defaultAssignment = HonorAssignment::where('tenant_id', $tenant->id)
            ->where('assignment_type', 'default')
            ->first();

        if ($defaultAssignment && $defaultAssignment->honorScheme) {
            $scheme = $defaultAssignment->honorScheme;
            $scheme->update([
                'method' => $method,
                'rate' => in_array($method, ['per_session', 'per_student']) ? $rate : 0,
                'fixed_amount' => $method === 'fixed_monthly' ? $rate : 0,
                'effective_from' => $effectiveFrom,
            ]);
        } else {
            $scheme = HonorScheme::create([
                'id' => (string) Str::uuid(),
                'tenant_id' => $tenant->id,
                'name' => 'Honor Default Bimbel',
                'method' => $method,
                'rate' => in_array($method, ['per_session', 'per_student']) ? $rate : 0,
                'fixed_amount' => $method === 'fixed_monthly' ? $rate : 0,
                'effective_from' => $effectiveFrom,
                'status' => 'active',
                'created_by' => $request->user()->id,
            ]);

            HonorAssignment::updateOrCreate([
                'tenant_id' => $tenant->id,
                'assignment_type' => 'default',
            ], [
                'id' => (string) Str::uuid(),
                'honor_scheme_id' => $scheme->id,
                'effective_from' => $effectiveFrom,
            ]);
        }

        return redirect()->route('owner.settings')->with('success', 'Pengaturan Honor Default Bimbel berhasil diperbarui.');
    }
}
