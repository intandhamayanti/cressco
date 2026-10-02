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
        Schema::create('assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('class_id');

            $table->string('name', 200);
            $table->enum('type', ['tugas', 'quiz', 'ujian']);
            $table->text('material')->nullable();
            $table->date('assessment_date');
            $table->decimal('max_score', 8, 2);
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_assessments_id_tenant');
            $table->index(['tenant_id', 'branch_id', 'assessment_date'], 'idx_assessments_tenant_branch_date');
            $table->index(['tenant_id', 'class_id', 'assessment_date'], 'idx_assessments_class_date');

            $table->foreign('tenant_id', 'fk_assessments_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_assessments_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['class_id', 'tenant_id'], 'fk_assessments_class_tenant')
                ->references(['id', 'tenant_id'])->on('classes')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('created_by', 'fk_assessments_created_by')
                ->references('id')->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE assessments ADD CONSTRAINT chk_assessments_max_score CHECK (max_score > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
