<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TeachingSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'schedule_id',
        'class_id',
        'scheduled_tutor_id',
        'actual_tutor_id',
        'session_date',
        'start_time',
        'end_time',
        'room',
        'status',
        'material',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'session_date' => 'date',
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

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function scheduledTutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_tutor_id');
    }

    public function actualTutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actual_tutor_id');
    }

    public function studentAttendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function tutorAttendance(): HasOne
    {
        return $this->hasOne(TutorAttendance::class);
    }

    public function tutorReplacements(): HasMany
    {
        return $this->hasMany(TutorReplacement::class);
    }
}
