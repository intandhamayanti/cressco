<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HonorScheme extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'name',
        'method',
        'rate',
        'percentage',
        'fixed_amount',
        'effective_from',
        'effective_until',
        'status',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'percentage' => 'decimal:2',
            'fixed_amount' => 'decimal:2',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'status' => 'string',
            'method' => 'string',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(HonorAssignment::class);
    }

    public function calculations(): HasMany
    {
        return $this->hasMany(HonorCalculation::class);
    }
}
