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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('student_id');
            $table->uuid('class_id');

            $table->date('started_at');
            $table->date('ended_at')->nullable();
            $table->enum('status', ['active', 'completed', 'withdrawn'])->default('active');
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_enrollments_id_tenant');
            $table->index(['tenant_id', 'branch_id', 'status'], 'idx_enrollments_tenant_branch_status');
            $table->index(['tenant_id', 'student_id', 'status'], 'idx_enrollments_student_status');
            $table->index(['tenant_id', 'class_id', 'status'], 'idx_enrollments_class_status');

            $table->foreign('tenant_id', 'fk_enrollments_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_enrollments_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['student_id', 'tenant_id'], 'fk_enrollments_student_tenant')
                ->references(['id', 'tenant_id'])->on('students')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['class_id', 'tenant_id'], 'fk_enrollments_class_tenant')
                ->references(['id', 'tenant_id'])->on('classes')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE enrollments ADD CONSTRAINT chk_enrollments_dates CHECK (ended_at IS NULL OR ended_at >= started_at)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
