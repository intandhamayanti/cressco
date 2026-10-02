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
        Schema::create('branch_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('user_id');
            $table->timestamps();

            $table->unique(['user_id', 'branch_id'], 'uq_branch_user_user_branch');
            $table->unique(['id', 'tenant_id'], 'uq_branch_user_id_tenant');
            $table->index(['tenant_id', 'branch_id'], 'idx_branch_user_tenant_branch');
            $table->index(['tenant_id', 'user_id'], 'idx_branch_user_tenant_user');

            $table->foreign('tenant_id', 'fk_branch_user_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_branch_user_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreign(['user_id', 'tenant_id'], 'fk_branch_user_user_tenant')
                ->references(['id', 'tenant_id'])->on('users')
                ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_user');
    }
};
