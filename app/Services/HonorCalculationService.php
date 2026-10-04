<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\HonorAssignment;
use App\Models\HonorCalculation;
use App\Models\HonorScheme;
use App\Models\Payment;
use App\Models\TeachingSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class HonorCalculationService
{
    /**
     * Resolve the active effective HonorScheme for a tutor during a given period.
     */
    public function resolveEffectiveScheme(User $tutor, string $periodStart, string $periodEnd): ?HonorScheme
    {
        $tenantId = $tutor->tenant_id;

        // 1. Check for specific tutor override assignment
        $tutorAssignment = HonorAssignment::where('tenant_id', $tenantId)
            ->where('assignment_type', 'tutor_override')
            ->where('tutor_id', $tutor->id)
            ->where('effective_from', '<=', $periodEnd)
            ->where(function ($q) use ($periodStart) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $periodStart);
            })
            ->with('honorScheme')
            ->latest('effective_from')
            ->first();

        if ($tutorAssignment && $tutorAssignment->honorScheme && $tutorAssignment->honorScheme->status === 'active') {
            return $tutorAssignment->honorScheme;
        }

        // 2. Check for default tenant assignment
        $defaultAssignment = HonorAssignment::where('tenant_id', $tenantId)
            ->where('assignment_type', 'default')
            ->where('effective_from', '<=', $periodEnd)
            ->where(function ($q) use ($periodStart) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $periodStart);
            })
            ->with('honorScheme')
            ->latest('effective_from')
            ->first();

        if ($defaultAssignment && $defaultAssignment->honorScheme && $defaultAssignment->honorScheme->status === 'active') {
            return $defaultAssignment->honorScheme;
        }

        // 3. Fallback to any active scheme in tenant
        return HonorScheme::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('effective_from', '<=', $periodEnd)
            ->where(function ($q) use ($periodStart) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $periodStart);
            })
            ->latest('effective_from')
            ->first();
    }

    /**
     * Calculate honor for a specific tutor over a date range.
     *
     * @throws ValidationException
     */
    public function calculateForTutor(
        User $tutor,
        string $periodStart,
        string $periodEnd,
        User $calculatedBy,
        ?string $branchId = null,
        ?HonorScheme $overrideScheme = null
    ): HonorCalculation {
        $tenantId = $tutor->tenant_id;

        if ($tutor->role !== 'tutor') {
            throw ValidationException::withMessages([
                'tutor_id' => 'Pengguna yang dipilih bukan seorang Tutor.',
            ]);
        }

        $scheme = $overrideScheme ?? $this->resolveEffectiveScheme($tutor, $periodStart, $periodEnd);

        if (! $scheme) {
            throw ValidationException::withMessages([
                'honor_scheme' => "Tidak ditemukan skema honor aktif untuk tutor {$tutor->name} pada periode ini.",
            ]);
        }

        // Query actual completed teaching sessions where this tutor was the actual teacher
        $sessionsQuery = TeachingSession::where('tenant_id', $tenantId)
            ->where('actual_tutor_id', $tutor->id)
            ->where('status', 'completed')
            ->whereBetween('session_date', [$periodStart, $periodEnd]);

        if ($branchId) {
            $sessionsQuery->where('branch_id', $branchId);
        }

        $completedSessions = $sessionsQuery->with(['studentAttendances', 'class.enrollments'])->get();
        $completedSessionsCount = $completedSessions->count();

        // Calculate base amount according to method
        $method = $scheme->method;
        $baseAmount = 0.0;

        switch ($method) {
            case 'per_session':
                $rate = (float) ($scheme->rate ?? 0);
                $fixedPart = (float) ($scheme->fixed_amount ?? 0);
                $baseAmount = $fixedPart + ($rate * $completedSessionsCount);
                break;

            case 'per_student':
                $rate = (float) ($scheme->rate ?? 0);
                $totalAttendedStudents = 0;
                foreach ($completedSessions as $session) {
                    $attendances = $session->studentAttendances;
                    if ($attendances->isNotEmpty()) {
                        $presentCount = $attendances->whereIn('status', ['hadir', 'present'])->count();
                        $totalAttendedStudents += $presentCount;
                    } else {
                        // Fallback to active enrollments in class if no attendances recorded
                        $totalAttendedStudents += $session->class?->enrollments?->where('status', 'active')->count() ?? 0;
                    }
                }
                $baseAmount = $rate * $totalAttendedStudents;
                break;

            case 'fixed_monthly':
                $fixedPart = (float) ($scheme->fixed_amount ?? $scheme->rate ?? 0);
                $sessionRate = (float) ($scheme->rate && $scheme->fixed_amount ? $scheme->rate : 0);
                $baseAmount = $fixedPart + ($sessionRate * $completedSessionsCount);
                break;

            case 'revenue_share':
                $percentage = (float) ($scheme->percentage ?? 0);
                $classIds = $completedSessions->pluck('class_id')->unique();
                $revenue = (float) Payment::where('tenant_id', $tenantId)
                    ->where('status', 'lunas')
                    ->whereHas('enrollment', function ($q) use ($classIds) {
                        $q->whereIn('class_id', $classIds);
                    })
                    ->whereBetween('paid_at', [
                        Carbon::parse($periodStart)->startOfDay(),
                        Carbon::parse($periodEnd)->endOfDay(),
                    ])
                    ->sum('amount');
                $baseAmount = ($percentage / 100) * $revenue;
                break;

            default:
                $rate = (float) ($scheme->rate ?? 0);
                $baseAmount = $rate * $completedSessionsCount;
                break;
        }

        $pStart = Carbon::parse($periodStart)->toDateString();
        $pEnd = Carbon::parse($periodEnd)->toDateString();

        // Check for existing duplicate calculation record
        $existing = HonorCalculation::where('tenant_id', $tenantId)
            ->where('tutor_id', $tutor->id)
            ->whereDate('period_start', $pStart)
            ->whereDate('period_end', $pEnd)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId), fn ($q) => $q->whereNull('branch_id'))
            ->first();

        if ($existing) {
            // If already finalized or paid, do not overwrite historical data
            if ($existing->status !== 'draft') {
                return $existing;
            }

            // Update existing draft
            $finalAmount = max(0, $baseAmount + (float) $existing->adjustment_amount);
            $existing->update([
                'honor_scheme_id' => $scheme->id,
                'method' => $method,
                'base_amount' => $baseAmount,
                'final_amount' => $finalAmount,
                'calculated_by' => $calculatedBy->id,
            ]);

            return $existing->fresh();
        }

        // Create new draft calculation
        return HonorCalculation::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'tutor_id' => $tutor->id,
            'honor_scheme_id' => $scheme->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'method' => $method,
            'base_amount' => $baseAmount,
            'adjustment_amount' => 0,
            'final_amount' => $baseAmount,
            'status' => 'draft',
            'calculated_by' => $calculatedBy->id,
        ]);
    }

    /**
     * Calculate honor for all active tutors in a tenant or accessible branches.
     *
     * @return Collection<int, HonorCalculation>
     */
    public function calculateForTenant(
        string $tenantId,
        string $periodStart,
        string $periodEnd,
        User $calculatedBy,
        ?array $accessibleBranchIds = null
    ): Collection {
        $tutorsQuery = User::where('tenant_id', $tenantId)
            ->where('role', 'tutor')
            ->where('status', 'active');

        if ($accessibleBranchIds !== null) {
            $tutorsQuery->whereHas('branches', function ($q) use ($accessibleBranchIds) {
                $q->whereIn('branches.id', $accessibleBranchIds);
            });
        }

        $tutors = $tutorsQuery->get();
        $results = new Collection;

        foreach ($tutors as $tutor) {
            try {
                // If branch scoping applies and tutor is associated with a single branch
                $branchId = ($accessibleBranchIds && count($accessibleBranchIds) === 1) ? $accessibleBranchIds[0] : null;
                $calc = $this->calculateForTutor($tutor, $periodStart, $periodEnd, $calculatedBy, $branchId);
                $results->push($calc);
            } catch (\Exception $e) {
                // Continue calculating for other tutors if one fails
                continue;
            }
        }

        return $results;
    }

    /**
     * Finalize an honor calculation.
     */
    public function finalizeCalculation(
        HonorCalculation $calculation,
        User $finalizedBy,
        ?float $adjustmentAmount = null,
        ?string $adjustmentReason = null
    ): HonorCalculation {
        $adjustment = $adjustmentAmount !== null ? $adjustmentAmount : (float) $calculation->adjustment_amount;
        $reason = $adjustmentReason !== null ? $adjustmentReason : $calculation->adjustment_reason;

        $finalAmount = max(0, (float) $calculation->base_amount + $adjustment);

        $calculation->update([
            'status' => 'final',
            'adjustment_amount' => $adjustment,
            'adjustment_reason' => $reason,
            'final_amount' => $finalAmount,
            'finalized_at' => now(),
            'finalized_by' => $finalizedBy->id,
        ]);

        return $calculation->fresh();
    }

    /**
     * Mark an honor calculation as paid.
     */
    public function markPaid(HonorCalculation $calculation): HonorCalculation
    {
        $calculation->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return $calculation->fresh();
    }
}
