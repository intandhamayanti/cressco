<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'student_id',
        'enrollment_id',
        'period',
        'amount',
        'due_date',
        'paid_at',
        'status',
        'notes',
        'recorded_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'status' => 'string',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function isPaid(): bool
    {
        return $this->status === 'lunas';
    }

    public function isPendingVerification(): bool
    {
        return $this->status === 'menunggu_verifikasi';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'terlambat' || ($this->status === 'belum_bayar' && $this->due_date && $this->due_date->isPast());
    }

    public function isUnpaid(): bool
    {
        return $this->status === 'belum_bayar';
    }

    /**
     * Scope for verified/paid payments
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'lunas');
    }

    /**
     * Scope for payments waiting verification
     */
    public function scopePendingVerification($query)
    {
        return $query->where('status', 'menunggu_verifikasi');
    }

    /**
     * Scope for overdue payments
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'terlambat')
            ->orWhere(function ($q) {
                $q->where('status', 'belum_bayar')
                    ->where('due_date', '<', now()->toDateString());
            });
    }

    /**
     * Scope for outstanding payments (belum_bayar, menunggu_verifikasi, terlambat)
     */
    public function scopeOutstanding($query)
    {
        return $query->whereIn('status', ['belum_bayar', 'menunggu_verifikasi', 'terlambat']);
    }

    /**
     * Scope for unpaid payments
     */
    public function scopeUnpaid($query)
    {
        return $query->where('status', 'belum_bayar');
    }
}
