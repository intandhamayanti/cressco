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
        Schema::create('honor_schemes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');

            $table->string('name', 150);
            $table->enum('method', ['per_session', 'per_student', 'revenue_share', 'fixed_monthly']);
            $table->decimal('rate', 15, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('fixed_amount', 15, 2)->nullable();
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->uuid('created_by');
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_honor_schemes_id_tenant');
            $table->index(['tenant_id', 'status'], 'idx_honor_schemes_tenant_status');
            $table->index(['tenant_id', 'effective_from', 'effective_until'], 'idx_honor_schemes_effective');

            $table->foreign('tenant_id', 'fk_honor_schemes_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('created_by', 'fk_honor_schemes_created_by')
                ->references('id')->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE honor_schemes ADD CONSTRAINT chk_honor_schemes_dates CHECK (effective_until IS NULL OR effective_until >= effective_from)');
            DB::statement('ALTER TABLE honor_schemes ADD CONSTRAINT chk_honor_schemes_percentage CHECK (percentage IS NULL OR (percentage >= 0 AND percentage <= 100))');
            DB::statement('ALTER TABLE honor_schemes ADD CONSTRAINT chk_honor_schemes_rate CHECK (rate IS NULL OR rate >= 0)');
            DB::statement('ALTER TABLE honor_schemes ADD CONSTRAINT chk_honor_schemes_fixed_amount CHECK (fixed_amount IS NULL OR fixed_amount >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honor_schemes');
    }
};
