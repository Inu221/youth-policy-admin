<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Platform\Models\User as OrchidUser;
use Orchid\Screen\AsSource;

class User extends OrchidUser
{
    use HasFactory;
    use SoftDeletes;
    use AsSource;

    public const ROLE_DIRECTOR = 'director';
    public const ROLE_DEPARTMENT_HEAD = 'department_head';
    public const ROLE_ANALYST = 'analyst';

    protected $fillable = [
        'department_id',
        'full_name',
        'name',
        'username',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'permissions' => 'array',
        'password' => 'hashed',
    ];

    protected $allowedSorts = [
        'id',
        'full_name',
        'name',
        'username',
        'email',
        'role',
        'department_id',
        'is_active',
        'last_login_at',
        'updated_at',
        'created_at',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function responsibleDepartment()
    {
        return $this->hasOne(Department::class, 'responsible_user_id');
    }

    public function createdAnnualPlans()
    {
        return $this->hasMany(AnnualPlan::class, 'created_by');
    }

    public function approvedAnnualPlans()
    {
        return $this->hasMany(AnnualPlan::class, 'approved_by');
    }

    public function responsiblePlannedEvents()
    {
        return $this->hasMany(PlannedEvent::class, 'responsible_user_id');
    }

    public function responsibleActualEvents()
    {
        return $this->hasMany(ActualEvent::class, 'responsible_user_id');
    }

    public function createdActualEvents()
    {
        return $this->hasMany(ActualEvent::class, 'created_by');
    }

    public function reviewedActualEventVerifications()
    {
        return $this->hasMany(ActualEventVerification::class, 'reviewer_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isDirector(): bool
    {
        return $this->role === self::ROLE_DIRECTOR;
    }

    public function isDepartmentHead(): bool
    {
        return $this->role === self::ROLE_DEPARTMENT_HEAD;
    }

    public function isAnalyst(): bool
    {
        return $this->role === self::ROLE_ANALYST;
    }
}