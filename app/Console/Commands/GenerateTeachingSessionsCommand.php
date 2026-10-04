<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TeachingSessionGenerationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateTeachingSessionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:generate
                            {--tenant= : Filter by Tenant ID or Slug}
                            {--branch= : Filter by Branch ID}
                            {--class= : Filter by Class ID}
                            {--from= : Start date (YYYY-MM-DD), default today}
                            {--to= : End date (YYYY-MM-DD), default --from + days}
                            {--days=14 : Number of days ahead to generate if --to is not specified}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate teaching sessions from active recurring schedules';

    public function handle(TeachingSessionGenerationService $generator): int
    {
        $tenantOption = $this->option('tenant');
        $branchOption = $this->option('branch');
        $classOption = $this->option('class');
        $fromOption = $this->option('from');
        $toOption = $this->option('to');
        $daysOption = (int) ($this->option('days') ?: 14);

        $startDate = $fromOption ? Carbon::parse($fromOption)->startOfDay() : Carbon::today();
        $endDate = $toOption ? Carbon::parse($toOption)->endOfDay() : $startDate->copy()->addDays($daysOption)->endOfDay();

        $this->info("Generating teaching sessions from {$startDate->toDateString()} to {$endDate->toDateString()}...");

        $tenantsQuery = Tenant::query()->where('status', 'active');
        if ($tenantOption) {
            $tenantsQuery->where(function ($q) use ($tenantOption) {
                $q->where('id', $tenantOption)->orWhere('slug', $tenantOption);
            });
        }

        $tenants = $tenantsQuery->get();

        if ($tenants->isEmpty()) {
            $this->warn('No active tenants found for session generation.');

            return self::SUCCESS;
        }

        $totalGenerated = 0;
        $totalSkipped = 0;

        foreach ($tenants as $tenant) {
            $filters = [];
            if ($branchOption) {
                $filters['branch_id'] = $branchOption;
            }
            if ($classOption) {
                $filters['class_id'] = $classOption;
            }

            $result = $generator->generateForTenant($tenant, $startDate, $endDate, $filters);

            $this->line("Tenant [{$tenant->name}]: Generated {$result['generated_count']} sessions, skipped {$result['skipped_existing_count']} existing/invalid.");

            $totalGenerated += $result['generated_count'];
            $totalSkipped += $result['skipped_existing_count'];
        }

        $this->newLine();
        $this->info("Completed session generation. Total new sessions: {$totalGenerated}, Skipped: {$totalSkipped}.");

        return self::SUCCESS;
    }
}
