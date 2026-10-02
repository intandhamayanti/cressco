<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HonorCalculation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'tutor_id',
        'honor_scheme_id',
        'period_start',
        'period_end',
        'method',
        'base_amount',
        'adjustment_amount',
        'final_amount',
        'status',
        'adjustment_reason',
        'finalized_at',
        'paid_at',
        'calculated_by',
        'finalized_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'base_amount' => 'decimal:2',
            'adjustment_amount' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'finalized_at' => 'datetime',
            'paid_at' => 'datetime',
            'status' => 'string',
            'method' => 'string',
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

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function honorScheme(): BelongsTo
    {
        return $this->belongsTo(HonorScheme::class);
    }

    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }
}
