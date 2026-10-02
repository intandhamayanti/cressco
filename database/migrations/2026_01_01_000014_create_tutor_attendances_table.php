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
        Schema::create('tutor_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('teaching_session_id');
            $table->uuid('tutor_id');

            $table->enum('status', ['present', 'absent'])->default('present');
            $table->dateTime('recorded_at');
            $table->string('source', 100)->default('student_attendance_submission');
            $table->timestamps();

            $table->unique('teaching_session_id', 'uq_tutor_attendance_session');
            $table->unique(['id', 'tenant_id'], 'uq_tutor_attendance_id_tenant');
            $table->index(['tenant_id', 'branch_id'], 'idx_tutor_attendance_tenant_branch');
            $table->index(['tenant_id', 'tutor_id'], 'idx_tutor_attendance_tutor');

            $table->foreign('tenant_id', 'fk_tutor_attendance_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_tutor_attendance_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['teaching_session_id', 'tenant_id'], 'fk_tutor_attendance_session_tenant')
                ->references(['id', 'tenant_id'])
                ->on('teaching_sessions')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['tutor_id', 'tenant_id'], 'fk_tutor_attendance_tutor_tenant')
                ->references(['id', 'tenant_id'])->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_attendances');
    }
};
