<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tutor\UpdateTutorPasswordRequest;
use App\Http\Requests\Tutor\UpdateTutorProfileRequest;
use App\Services\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TutorProfileController extends Controller
{
    public function show(Request $request): View
    {
        $tutor = $request->user();
        $tenant = TenantContext::getTenant() ?? $tutor->tenant;

        $assignedClasses = $tutor->assignedClasses()
            ->with('branch')
            ->wherePivot('status', 'active')
            ->orderBy('name')
            ->get();

        $totalAssignedClasses = $assignedClasses->count();
        $completedSessionsCount = $tutor->actualTeachingSessions()
            ->where('status', 'completed')
            ->count();

        return view('tutor.profile.index', [
            'tenant' => $tenant,
            'user' => $tutor,
            'assignedClasses' => $assignedClasses,
            'totalAssignedClasses' => $totalAssignedClasses,
            'completedSessionsCount' => $completedSessionsCount,
        ]);
    }

    public function update(UpdateTutorProfileRequest $request): RedirectResponse
    {
        $tutor = $request->user();
        $validated = $request->validated();

        if ($tutor->email !== $validated['email']) {
            $tutor->email_verified_at = null;
        }

        $tutor->update($validated);

        return redirect()->route('tutor.profile')->with('success', 'Profil Tutor berhasil diperbarui.');
    }

    public function updatePassword(UpdateTutorPasswordRequest $request): RedirectResponse
    {
        $tutor = $request->user();

        $tutor->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('tutor.profile')->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
