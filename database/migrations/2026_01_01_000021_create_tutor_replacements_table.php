<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tutor_replacements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('teaching_session_id');
            $table->uuid('scheduled_tutor_id');
            $table->uuid('previous_actual_tutor_id')->nullable();
            $table->uuid('replacement_tutor_id');

            $table->text('reason');
            $table->uuid('changed_by');
            $table->dateTime('changed_at');
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->unique(['id', 'tenant_id'], 'uq_tutor_replacements_id_tenant');
            $table->index(['tenant_id', 'teaching_session_id'], 'idx_tutor_replacements_session');
            $table->index(['tenant_id', 'branch_id'], 'idx_tutor_replacements_branch');
            $table->index(['tenant_id', 'changed_at'], 'idx_tutor_replacements_changed_at');

            $table->foreign('tenant_id', 'fk_tutor_replacements_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_tutor_replacements_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['teaching_session_id', 'tenant_id'], 'fk_tutor_replacements_session_tenant')
                ->references(['id', 'tenant_id'])->on('teaching_sessions')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['scheduled_tutor_id', 'tenant_id'], 'fk_tutor_replacements_scheduled_tutor')
                ->references(['id', 'tenant_id'])->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['previous_actual_tutor_id', 'tenant_id'], 'fk_tutor_replacements_previous_actual_tutor')
                ->references(['id', 'tenant_id'])->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['replacement_tutor_id', 'tenant_id'], 'fk_tutor_replacements_replacement_tutor')
                ->references(['id', 'tenant_id'])->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('changed_by', 'fk_tutor_replacements_changed_by')
                ->references('id')->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_replacements');
    }
};
