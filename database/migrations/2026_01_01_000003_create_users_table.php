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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->string('name', 150);
            $table->string('email', 255)->unique('uq_users_email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->enum('role', ['owner', 'admin', 'tutor', 'super_admin']);
            $table->enum('status', ['active', 'inactive', 'invited'])->default('invited');
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_users_id_tenant');
            $table->index(['tenant_id', 'role'], 'idx_users_tenant_role');
            $table->index(['tenant_id', 'status'], 'idx_users_tenant_status');

            $table->foreign('tenant_id', 'fk_users_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 255)->primary();
            $table->string('token', 255);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->uuid('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity');

            $table->index('user_id', 'idx_sessions_user_id');
            $table->index('last_activity', 'idx_sessions_last_activity');

            $table->foreign('user_id', 'fk_sessions_user')
                ->references('id')->on('users')
                ->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
