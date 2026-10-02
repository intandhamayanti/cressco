<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'class_id',
        'scheduled_tutor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'starts_on',
        'ends_on',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'starts_on' => 'date',
            'ends_on' => 'date',
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

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function scheduledTutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_tutor_id');
    }

    public function teachingSessions(): HasMany
    {
        return $this->hasMany(TeachingSession::class);
    }
}
