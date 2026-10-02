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
        Schema::create('honor_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('tutor_id')->nullable();
            $table->uuid('honor_scheme_id');

            $table->enum('assignment_type', ['default', 'tutor_override']);
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_honor_assignments_id_tenant');
            $table->index(['tenant_id', 'assignment_type'], 'idx_honor_assignments_tenant_type');
            $table->index(['tenant_id', 'tutor_id'], 'idx_honor_assignments_tutor');
            $table->index(['tenant_id', 'honor_scheme_id'], 'idx_honor_assignments_scheme');
            $table->index(['tenant_id', 'effective_from', 'effective_until'], 'idx_honor_assignments_effective');

            $table->foreign('tenant_id', 'fk_honor_assignments_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['tutor_id', 'tenant_id'], 'fk_honor_assignments_tutor_tenant')
                ->references(['id', 'tenant_id'])->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['honor_scheme_id', 'tenant_id'], 'fk_honor_assignments_scheme_tenant')
                ->references(['id', 'tenant_id'])->on('honor_schemes')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE honor_assignments ADD CONSTRAINT chk_honor_assignments_dates CHECK (effective_until IS NULL OR effective_until >= effective_from)');
            DB::statement("ALTER TABLE honor_assignments ADD CONSTRAINT chk_honor_assignments_type_tutor CHECK ((assignment_type = 'default' AND tutor_id IS NULL) OR (assignment_type = 'tutor_override' AND tutor_id IS NOT NULL))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('honor_assignments');
    }
};
