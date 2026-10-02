<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('class_id');
            $table->uuid('scheduled_tutor_id');

            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 150)->nullable();

            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_schedules_id_tenant');
            $table->index(['tenant_id', 'branch_id', 'status'], 'idx_schedules_tenant_branch_status');
            $table->index(['tenant_id', 'class_id', 'status'], 'idx_schedules_class_status');
            $table->index(['tenant_id', 'scheduled_tutor_id', 'status'], 'idx_schedules_tutor_status');

            $table->foreign('tenant_id', 'fk_schedules_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_schedules_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['class_id', 'tenant_id'], 'fk_schedules_class_tenant')
                ->references(['id', 'tenant_id'])->on('classes')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['scheduled_tutor_id', 'tenant_id'], 'fk_schedules_tutor_tenant')
                ->references(['id', 'tenant_id'])->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE schedules ADD CONSTRAINT chk_schedules_day_of_week CHECK (day_of_week BETWEEN 0 AND 6)');
            DB::statement('ALTER TABLE schedules ADD CONSTRAINT chk_schedules_time CHECK (end_time > start_time)');
            DB::statement('ALTER TABLE schedules ADD CONSTRAINT chk_schedules_dates CHECK (ends_on IS NULL OR ends_on >= starts_on)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
