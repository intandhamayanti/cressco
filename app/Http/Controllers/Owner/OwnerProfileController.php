<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdatePasswordRequest;
use App\Http\Requests\Owner\UpdateProfileRequest;
use App\Models\Branch;
use App\Models\Student;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OwnerProfileController extends Controller
{
    public function index(Request $request): View
    {
        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;
        $user = $request->user();

        $branchCount = Branch::where('tenant_id', $tenant->id)->count();
        $studentCount = Student::where('tenant_id', $tenant->id)->count();
        $tutorCount = User::where('tenant_id', $tenant->id)->where('role', 'tutor')->count();

        return view('owner.profile.index', [
            'tenant' => $tenant,
            'user' => $user,
            'stats' => [
                'branchCount' => $branchCount,
                'studentCount' => $studentCount,
                'tutorCount' => $tutorCount,
            ],
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

        return redirect()->route('owner.profile')->with('success', 'Profil akun Anda berhasil diperbarui.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('owner.profile')->with('success', 'Kata sandi akun Anda berhasil diubah.');
    }
}
