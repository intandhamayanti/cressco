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
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('teaching_session_id');
            $table->uuid('student_id');

            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa']);
            $table->text('note')->nullable();
            $table->dateTime('recorded_at');
            $table->uuid('recorded_by');
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->unique(['teaching_session_id', 'student_id'], 'uq_student_attendance_session_student');
            $table->unique(['id', 'tenant_id'], 'uq_student_attendance_id_tenant');
            $table->index(['tenant_id', 'branch_id'], 'idx_student_attendance_tenant_branch');
            $table->index(['tenant_id', 'teaching_session_id'], 'idx_student_attendance_session');
            $table->index(['tenant_id', 'student_id'], 'idx_student_attendance_student');

            $table->foreign('tenant_id', 'fk_student_attendance_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_student_attendance_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['teaching_session_id', 'tenant_id'], 'fk_student_attendance_session_tenant')
                ->references(['id', 'tenant_id'])->on('teaching_sessions')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['student_id', 'tenant_id'], 'fk_student_attendance_student_tenant')
                ->references(['id', 'tenant_id'])->on('students')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('recorded_by', 'fk_student_attendance_recorded_by')
                ->references('id')->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendances');
    }
};
