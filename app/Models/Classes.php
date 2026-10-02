<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classes extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'classes';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'subject',
        'level',
        'capacity',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
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

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'enrollments', 'class_id', 'student_id')
            ->withPivot('id', 'tenant_id', 'branch_id', 'started_at', 'ended_at', 'status')
            ->withTimestamps();
    }

    public function tutorAssignments(): HasMany
    {
        return $this->hasMany(TutorAssignment::class, 'class_id');
    }

    public function tutors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tutor_assignments', 'class_id', 'tutor_id')
            ->withPivot('id', 'tenant_id', 'branch_id', 'started_at', 'ended_at', 'status')
            ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    public function teachingSessions(): HasMany
    {
        return $this->hasMany(TeachingSession::class, 'class_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'class_id');
    }
}
