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
        Schema::create('teaching_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('schedule_id')->nullable();
            $table->uuid('class_id');
            $table->uuid('scheduled_tutor_id');
            $table->uuid('actual_tutor_id')->nullable();

            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 150)->nullable();

            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->text('material')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_teaching_sessions_id_tenant');
            $table->unique(['schedule_id', 'session_date'], 'uq_teaching_sessions_schedule_date');
            $table->index(['tenant_id', 'branch_id', 'session_date'], 'idx_teaching_sessions_tenant_branch_date');
            $table->index(['tenant_id', 'class_id', 'session_date'], 'idx_teaching_sessions_class_date');
            $table->index(['tenant_id', 'scheduled_tutor_id', 'session_date'], 'idx_teaching_sessions_scheduled_tutor_date');
            $table->index(['tenant_id', 'actual_tutor_id', 'session_date'], 'idx_teaching_sessions_actual_tutor_date');
            $table->index(['tenant_id', 'status', 'session_date'], 'idx_teaching_sessions_status_date');

            $table->foreign('tenant_id', 'fk_teaching_sessions_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_teaching_sessions_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['schedule_id', 'tenant_id'], 'fk_teaching_sessions_schedule_tenant')
                ->references(['id', 'tenant_id'])->on('schedules')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['class_id', 'tenant_id'], 'fk_teaching_sessions_class_tenant')
                ->references(['id', 'tenant_id'])->on('classes')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['scheduled_tutor_id', 'tenant_id'], 'fk_teaching_sessions_scheduled_tutor_tenant')
                ->references(['id', 'tenant_id'])->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['actual_tutor_id', 'tenant_id'], 'fk_teaching_sessions_actual_tutor_tenant')
                ->references(['id', 'tenant_id'])->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE teaching_sessions ADD CONSTRAINT chk_teaching_sessions_time CHECK (end_time > start_time)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teaching_sessions');
    }
};
