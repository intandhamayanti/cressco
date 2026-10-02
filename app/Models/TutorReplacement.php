<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutorReplacement extends Model
{
    use HasFactory, HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'teaching_session_id',
        'scheduled_tutor_id',
        'previous_actual_tutor_id',
        'replacement_tutor_id',
        'reason',
        'changed_by',
        'changed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
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

    public function teachingSession(): BelongsTo
    {
        return $this->belongsTo(TeachingSession::class);
    }

    public function scheduledTutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_tutor_id');
    }

    public function previousActualTutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_actual_tutor_id');
    }

    public function replacementTutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replacement_tutor_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
