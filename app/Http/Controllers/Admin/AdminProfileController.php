<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePasswordRequest;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function show(Request $request): View
    {
        $admin = $request->user();
        $tenant = TenantContext::getTenant() ?? $admin->tenant;
        $branches = $admin->branches()->orderBy('name')->get();

        return view('admin.profile.index', [
            'tenant' => $tenant,
            'user' => $admin,
            'branches' => $branches,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $admin = $request->user();
        $validated = $request->validated();

        if ($admin->email !== $validated['email']) {
            $admin->email_verified_at = null;
        }

        $admin->update($validated);

        return redirect()->route('admin.profile')->with('success', 'Profil Admin berhasil diperbarui.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $admin = $request->user();

        $admin->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('admin.profile')->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
