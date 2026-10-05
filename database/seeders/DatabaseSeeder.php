<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This is a DEMO seeder with known credentials and must never run in production.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('DatabaseSeeder is a demo seeder and is disabled in production.');

            return;
        }

        $this->call(DemoDataSeeder::class);
    }
}
