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
        Schema::create('tutor_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('tutor_id');
            $table->uuid('class_id');

            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_tutor_assignments_id_tenant');
            $table->index(['tenant_id', 'branch_id', 'status'], 'idx_tutor_assignments_tenant_branch_status');
            $table->index(['tenant_id', 'tutor_id', 'status'], 'idx_tutor_assignments_tutor_status');
            $table->index(['tenant_id', 'class_id', 'status'], 'idx_tutor_assignments_class_status');

            $table->foreign('tenant_id', 'fk_tutor_assignments_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_tutor_assignments_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['tutor_id', 'tenant_id'], 'fk_tutor_assignments_tutor_tenant')
                ->references(['id', 'tenant_id'])->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['class_id', 'tenant_id'], 'fk_tutor_assignments_class_tenant')
                ->references(['id', 'tenant_id'])->on('classes')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE tutor_assignments ADD CONSTRAINT chk_tutor_assignments_dates CHECK (ended_at IS NULL OR started_at IS NULL OR ended_at >= started_at)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_assignments');
    }
};
