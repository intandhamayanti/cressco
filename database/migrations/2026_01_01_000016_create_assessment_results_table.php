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
        Schema::create('assessment_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('assessment_id');
            $table->uuid('student_id');

            $table->decimal('score', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'student_id'], 'uq_assessment_results_assessment_student');
            $table->unique(['id', 'tenant_id'], 'uq_assessment_results_id_tenant');
            $table->index(['tenant_id', 'branch_id'], 'idx_assessment_results_tenant_branch');
            $table->index(['tenant_id', 'assessment_id'], 'idx_assessment_results_assessment');
            $table->index(['tenant_id', 'student_id'], 'idx_assessment_results_student');

            $table->foreign('tenant_id', 'fk_assessment_results_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_assessment_results_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['assessment_id', 'tenant_id'], 'fk_assessment_results_assessment_tenant')
                ->references(['id', 'tenant_id'])->on('assessments')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['student_id', 'tenant_id'], 'fk_assessment_results_student_tenant')
                ->references(['id', 'tenant_id'])->on('students')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE assessment_results ADD CONSTRAINT chk_assessment_results_score CHECK (score >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_results');
    }
};
