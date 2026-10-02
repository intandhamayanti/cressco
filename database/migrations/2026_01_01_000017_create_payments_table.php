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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('branch_id');
            $table->uuid('student_id');
            $table->uuid('enrollment_id')->nullable();

            $table->string('period', 30);
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->dateTime('paid_at')->nullable();
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'terlambat'])->default('belum_bayar');
            $table->text('notes')->nullable();
            $table->uuid('recorded_by');
            $table->timestamps();

            $table->unique(['id', 'tenant_id'], 'uq_payments_id_tenant');
            $table->index(['tenant_id', 'branch_id', 'status'], 'idx_payments_tenant_branch_status');
            $table->index(['tenant_id', 'student_id', 'period'], 'idx_payments_student_period');
            $table->index(['tenant_id', 'enrollment_id', 'period'], 'idx_payments_enrollment_period');
            $table->index(['tenant_id', 'due_date'], 'idx_payments_due_date');
            $table->index(['tenant_id', 'paid_at'], 'idx_payments_paid_at');

            $table->foreign('tenant_id', 'fk_payments_tenant')
                ->references('id')->on('tenants')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['branch_id', 'tenant_id'], 'fk_payments_branch_tenant')
                ->references(['id', 'tenant_id'])->on('branches')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['student_id', 'tenant_id'], 'fk_payments_student_tenant')
                ->references(['id', 'tenant_id'])->on('students')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign(['enrollment_id', 'tenant_id'], 'fk_payments_enrollment_tenant')
                ->references(['id', 'tenant_id'])->on('enrollments')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('recorded_by', 'fk_payments_recorded_by')
                ->references('id')->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE payments ADD CONSTRAINT chk_payments_amount CHECK (amount >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
