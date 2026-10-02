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
        Schema::create('honor_calculations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id')->nullable();
            $table->uuid('tutor_id');
            $table->uuid('honor_scheme_id');

            $table->date('period_start');
            $table->date('period_end');
            $table->enum('method', ['per_session', 'per_student', 'revenue_share', 'fixed_monthly']);

            $table->decimal('base_amount', 15, 2)->default(0);
            $table->decimal('adjustment_amount', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2)->default(0);

            $table->enum('status', ['draft', 'final', 'paid'])->default('draft');
            $table->text('adjustment_reason')->nullable();

            $table->dateTime('finalized_at')->nullable();
            $table->dateTime('paid_at')->nullable();

            $table->uuid('calculated_by');
            $table->uuid('finalized_by')->nullable();
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_honor_calculations_id_tenant');
            $table->index(['tenant_id', 'branch_id', 'period_start', 'period_end'], 'idx_honor_calculations_tenant_branch_period');
            $table->index(['tenant_id', 'tutor_id', 'period_start', 'period_end'], 'idx_honor_calculations_tutor_period');
            $table->index(['tenant_id', 'status'], 'idx_honor_calculations_status');

            $table->foreign('tenant_id', 'fk_honor_calculations_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_honor_calculations_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['tutor_id', 'tenant_id'], 'fk_honor_calculations_tutor_tenant')
                ->references(['id', 'tenant_id'])->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['honor_scheme_id', 'tenant_id'], 'fk_honor_calculations_scheme_tenant')
                ->references(['id', 'tenant_id'])->on('honor_schemes')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('calculated_by', 'fk_honor_calculations_calculated_by')
                ->references('id')->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('finalized_by', 'fk_honor_calculations_finalized_by')
                ->references('id')->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE honor_calculations ADD CONSTRAINT chk_honor_calculations_period CHECK (period_end >= period_start)');
            DB::statement('ALTER TABLE honor_calculations ADD CONSTRAINT chk_honor_calculations_amounts CHECK (base_amount >= 0 AND final_amount >= 0)');
            DB::statement('ALTER TABLE honor_calculations ADD CONSTRAINT chk_honor_calculations_adjustment_reason CHECK (adjustment_amount = 0 OR adjustment_reason IS NOT NULL)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honor_calculations');
    }
};
