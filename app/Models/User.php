<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
            'status' => 'string',
        ];
    }

    // ==========================================
    // Role & Identity Checks
    // ==========================================

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTutor(): bool
    {
        return $this->role === 'tutor';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * @param  string|array<int, string>  $roles
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }

        return $this->role === $roles;
    }

    public function isTenantUser(): bool
    {
        return in_array($this->role, ['owner', 'admin', 'tutor'], true) && ! empty($this->tenant_id);
    }

    // ==========================================
    // Branch Scope & Teaching Scope Checks
    // ==========================================

    /**
     * Check if user has access to a specific branch.
     * Owner has tenant-wide branch access.
     * Admin has explicit branch access via branch_user.
     * Tutor does not have generic branch access.
     */
    public function hasBranchAccess(Branch|string $branch): bool
    {
        $branchId = $branch instanceof Branch ? $branch->id : $branch;

        if ($this->isOwner()) {
            if ($branch instanceof Branch) {
                return $branch->tenant_id === $this->tenant_id;
            }

            return Branch::query()
                ->where('id', $branchId)
                ->where('tenant_id', $this->tenant_id)
                ->exists();
        }

        if ($this->isAdmin()) {
            return $this->branches()
                ->where('branches.id', $branchId)
                ->where('branches.tenant_id', $this->tenant_id)
                ->exists();
        }

        return false;
    }

    /**
     * Get collection of accessible branch IDs.
     *
     * @return Collection<int, string>
     */
    public function accessibleBranchIds(): Collection
    {
        if ($this->isOwner()) {
            return Branch::query()
                ->where('tenant_id', $this->tenant_id)
                ->pluck('id');
        }

        if ($this->isAdmin()) {
            return $this->branches()
                ->where('branches.tenant_id', $this->tenant_id)
                ->pluck('branches.id');
        }

        return collect();
    }

    /**
     * Check if tutor is assigned to teach a class.
     */
    public function canTeachClass(Classes|string $class): bool
    {
        if (! $this->isTutor()) {
            return false;
        }

        $classId = $class instanceof Classes ? $class->id : $class;

        return $this->tutorAssignments()
            ->where('class_id', $classId)
            ->where('tenant_id', $this->tenant_id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Check if user can access a specific teaching session.
     */
    public function canAccessTeachingSession(TeachingSession $session): bool
    {
        if ($session->tenant_id !== $this->tenant_id) {
            return false;
        }

        if ($this->isOwner()) {
            return true;
        }

        if ($this->isAdmin()) {
            return $this->hasBranchAccess($session->branch_id);
        }

        if ($this->isTutor()) {
            return $session->scheduled_tutor_id === $this->id
                || $session->actual_tutor_id === $this->id
                || $this->canTeachClass($session->class_id);
        }

        return false;
    }

    // ==========================================
    // Eloquent Relationships
    // ==========================================

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user')
            ->withPivot('id', 'tenant_id')
            ->withTimestamps();
    }

    public function branchUsers(): HasMany
    {
        return $this->hasMany(BranchUser::class);
    }

    public function tutorAssignments(): HasMany
    {
        return $this->hasMany(TutorAssignment::class, 'tutor_id');
    }

    public function assignedClasses(): BelongsToMany
    {
        return $this->belongsToMany(Classes::class, 'tutor_assignments', 'tutor_id', 'class_id')
            ->withPivot('id', 'tenant_id', 'branch_id', 'started_at', 'ended_at', 'status')
            ->withTimestamps();
    }

    public function scheduledSchedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'scheduled_tutor_id');
    }

    public function scheduledTeachingSessions(): HasMany
    {
        return $this->hasMany(TeachingSession::class, 'scheduled_tutor_id');
    }

    public function actualTeachingSessions(): HasMany
    {
        return $this->hasMany(TeachingSession::class, 'actual_tutor_id');
    }

    public function recordedStudentAttendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'recorded_by');
    }

    public function tutorAttendances(): HasMany
    {
        return $this->hasMany(TutorAttendance::class, 'tutor_id');
    }

    public function createdAssessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'created_by');
    }

    public function recordedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'recorded_by');
    }

    public function createdHonorSchemes(): HasMany
    {
        return $this->hasMany(HonorScheme::class, 'created_by');
    }

    public function honorAssignments(): HasMany
    {
        return $this->hasMany(HonorAssignment::class, 'tutor_id');
    }

    public function calculatedHonorCalculations(): HasMany
    {
        return $this->hasMany(HonorCalculation::class, 'calculated_by');
    }

    public function finalizedHonorCalculations(): HasMany
    {
        return $this->hasMany(HonorCalculation::class, 'finalized_by');
    }

    public function tutorHonorCalculations(): HasMany
    {
        return $this->hasMany(HonorCalculation::class, 'tutor_id');
    }

    public function scheduledTutorReplacements(): HasMany
    {
        return $this->hasMany(TutorReplacement::class, 'scheduled_tutor_id');
    }

    public function previousActualTutorReplacements(): HasMany
    {
        return $this->hasMany(TutorReplacement::class, 'previous_actual_tutor_id');
    }

    public function replacementTutorReplacements(): HasMany
    {
        return $this->hasMany(TutorReplacement::class, 'replacement_tutor_id');
    }

    public function changedTutorReplacements(): HasMany
    {
        return $this->hasMany(TutorReplacement::class, 'changed_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_user_id');
    }
}
