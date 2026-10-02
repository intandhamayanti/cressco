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
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');

            $table->string('name', 150);
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('parent_name', 150)->nullable();
            $table->string('parent_phone', 50)->nullable();
            $table->text('notes')->nullable();
            $table->date('joined_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_students_id_tenant');
            $table->index(['tenant_id', 'branch_id', 'status'], 'idx_students_tenant_branch_status');
            $table->index(['tenant_id', 'parent_phone'], 'idx_students_parent_phone');
            $table->index(['tenant_id', 'name'], 'idx_students_name');

            $table->foreign('tenant_id', 'fk_students_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_students_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
