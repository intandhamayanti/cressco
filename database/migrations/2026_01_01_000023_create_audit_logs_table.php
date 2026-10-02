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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->uuid('actor_user_id')->nullable();

            $table->string('action', 100);
            $table->string('entity_type', 150);
            $table->uuid('entity_id')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->index(['tenant_id', 'created_at'], 'idx_audit_logs_tenant_created');
            $table->index(['actor_user_id', 'created_at'], 'idx_audit_logs_actor_created');
            $table->index(['entity_type', 'entity_id'], 'idx_audit_logs_entity');
            $table->index('action', 'idx_audit_logs_action');

            $table->foreign('tenant_id', 'fk_audit_logs_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->nullOnDelete();

            $table->foreign('actor_user_id', 'fk_audit_logs_actor')
                ->references('id')->on('users')
                ->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
