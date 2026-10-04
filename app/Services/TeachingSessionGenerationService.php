<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Classes;
use App\Models\Schedule;
use App\Models\TeachingSession;
use App\Models\Tenant;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeachingSessionGenerationService
{
    /**
     * Generate teaching sessions for a single recurring schedule within a date range.
     *
     * @return array{generated_count: int, skipped_existing_count: int, sessions: Collection<int, TeachingSession>}
     */
    public function generateForSchedule(
        Schedule $schedule,
        CarbonInterface|string $startDate,
        CarbonInterface|string $endDate
    ): array {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        if ($start->gt($end)) {
            return [
                'generated_count' => 0,
                'skipped_existing_count' => 0,
                'sessions' => collect(),
            ];
        }

        // Schedule must be active and have a valid day of week
        if ($schedule->status !== 'active') {
            return [
                'generated_count' => 0,
                'skipped_existing_count' => 0,
                'sessions' => collect(),
            ];
        }

        // Ensure schedule's class and tenant are active if loaded
        if ($schedule->relationLoaded('class') && $schedule->class && $schedule->class->status !== 'active') {
            return [
                'generated_count' => 0,
                'skipped_existing_count' => 0,
                'sessions' => collect(),
            ];
        }

        $scheduleStartsOn = $schedule->starts_on ? Carbon::parse($schedule->starts_on)->startOfDay() : null;
        $scheduleEndsOn = $schedule->ends_on ? Carbon::parse($schedule->ends_on)->endOfDay() : null;

        $targetDayOfWeek = (int) $schedule->day_of_week;
        $period = CarbonPeriod::create($start, '1 day', $end);

        $generatedCount = 0;
        $skippedExistingCount = 0;
        $generatedSessions = collect();

        DB::transaction(function () use (
            $period,
            $schedule,
            $targetDayOfWeek,
            $scheduleStartsOn,
            $scheduleEndsOn,
            &$generatedCount,
            &$skippedExistingCount,
            &$generatedSessions
        ) {
            foreach ($period as $date) {
                /** @var Carbon $date */
                // 1. Day of week check (0 = Sunday, 1 = Monday, ... 6 = Saturday)
                if ($date->dayOfWeek !== $targetDayOfWeek) {
                    continue;
                }

                // 2. Schedule active validity period check
                if ($scheduleStartsOn && $date->lt($scheduleStartsOn)) {
                    continue;
                }
                if ($scheduleEndsOn && $date->gt($scheduleEndsOn)) {
                    continue;
                }

                $sessionDate = $date->toDateString();

                // 3. Duplicate check using unique constraint (schedule_id, session_date)
                $existing = TeachingSession::query()
                    ->where('schedule_id', $schedule->id)
                    ->whereDate('session_date', $sessionDate)
                    ->first();

                if ($existing) {
                    $skippedExistingCount++;

                    continue;
                }

                $newSession = TeachingSession::create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => $schedule->tenant_id,
                    'branch_id' => $schedule->branch_id,
                    'schedule_id' => $schedule->id,
                    'class_id' => $schedule->class_id,
                    'scheduled_tutor_id' => $schedule->scheduled_tutor_id,
                    'actual_tutor_id' => $schedule->scheduled_tutor_id,
                    'session_date' => $sessionDate,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'room' => $schedule->room,
                    'status' => 'scheduled',
                ]);

                $generatedSessions->push($newSession);
                $generatedCount++;
            }
        });

        return [
            'generated_count' => $generatedCount,
            'skipped_existing_count' => $skippedExistingCount,
            'sessions' => $generatedSessions,
        ];
    }

    /**
     * Generate teaching sessions for all active schedules of a specific class.
     *
     * @return array{generated_count: int, skipped_existing_count: int, sessions: Collection<int, TeachingSession>}
     */
    public function generateForClass(
        Classes $class,
        CarbonInterface|string $startDate,
        CarbonInterface|string $endDate
    ): array {
        if ($class->status !== 'active') {
            return [
                'generated_count' => 0,
                'skipped_existing_count' => 0,
                'sessions' => collect(),
            ];
        }

        $schedules = Schedule::query()
            ->where('tenant_id', $class->tenant_id)
            ->where('class_id', $class->id)
            ->where('status', 'active')
            ->get();

        return $this->generateForScheduleCollection($schedules, $startDate, $endDate);
    }

    /**
     * Generate teaching sessions for all active schedules of a branch.
     *
     * @return array{generated_count: int, skipped_existing_count: int, sessions: Collection<int, TeachingSession>}
     */
    public function generateForBranch(
        Branch $branch,
        CarbonInterface|string $startDate,
        CarbonInterface|string $endDate
    ): array {
        if ($branch->status !== 'active') {
            return [
                'generated_count' => 0,
                'skipped_existing_count' => 0,
                'sessions' => collect(),
            ];
        }

        $schedules = Schedule::query()
            ->where('tenant_id', $branch->tenant_id)
            ->where('branch_id', $branch->id)
            ->where('status', 'active')
            ->whereHas('class', function ($q) {
                $q->where('status', 'active');
            })
            ->get();

        return $this->generateForScheduleCollection($schedules, $startDate, $endDate);
    }

    /**
     * Generate teaching sessions for a tenant with optional filtering.
     *
     * @param  array{branch_id?: ?string, class_id?: ?string, scheduled_tutor_id?: ?string}  $filters
     * @return array{generated_count: int, skipped_existing_count: int, sessions: Collection<int, TeachingSession>}
     */
    public function generateForTenant(
        Tenant|string $tenant,
        CarbonInterface|string $startDate,
        CarbonInterface|string $endDate,
        array $filters = []
    ): array {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        $tenantModel = $tenant instanceof Tenant ? $tenant : Tenant::find($tenantId);
        if ($tenantModel && $tenantModel->status !== 'active') {
            return [
                'generated_count' => 0,
                'skipped_existing_count' => 0,
                'sessions' => collect(),
            ];
        }

        $query = Schedule::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereHas('class', function ($q) {
                $q->where('status', 'active');
            });

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (! empty($filters['scheduled_tutor_id'])) {
            $query->where('scheduled_tutor_id', $filters['scheduled_tutor_id']);
        }

        $schedules = $query->get();

        return $this->generateForScheduleCollection($schedules, $startDate, $endDate);
    }

    /**
     * Helper to run generation on a collection of schedules.
     *
     * @param  Collection<int, Schedule>  $schedules
     * @return array{generated_count: int, skipped_existing_count: int, sessions: Collection<int, TeachingSession>}
     */
    protected function generateForScheduleCollection(
        Collection $schedules,
        CarbonInterface|string $startDate,
        CarbonInterface|string $endDate
    ): array {
        $totalGenerated = 0;
        $totalSkipped = 0;
        $allSessions = collect();

        foreach ($schedules as $schedule) {
            $result = $this->generateForSchedule($schedule, $startDate, $endDate);
            $totalGenerated += $result['generated_count'];
            $totalSkipped += $result['skipped_existing_count'];
            $allSessions = $allSessions->concat($result['sessions']);
        }

        return [
            'generated_count' => $totalGenerated,
            'skipped_existing_count' => $totalSkipped,
            'sessions' => $allSessions,
        ];
    }
}
