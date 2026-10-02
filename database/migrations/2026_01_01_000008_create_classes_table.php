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
        Schema::create('classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');

            $table->string('name', 150);
            $table->string('subject', 150)->nullable();
            $table->string('level', 100)->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_classes_id_tenant');
            $table->index(['tenant_id', 'branch_id', 'status'], 'idx_classes_tenant_branch_status');
            $table->index(['tenant_id', 'name'], 'idx_classes_tenant_name');

            $table->foreign('tenant_id', 'fk_classes_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_classes_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE classes ADD CONSTRAINT chk_classes_capacity CHECK (capacity IS NULL OR capacity > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
