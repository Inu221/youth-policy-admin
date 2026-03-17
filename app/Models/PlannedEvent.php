<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;

class PlannedEvent extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AsSource;

    public const STATUS_PLANNED = 'planned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'annual_plan_id',
        'title',
        'description',
        'planned_start_at',
        'planned_end_at',
        'location_name',
        'location_url',
        'responsible_user_id',
        'planned_participants_count',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'planned_start_at' => 'datetime',
        'planned_end_at' => 'datetime',
    ];

    public function annualPlan()
    {
        return $this->belongsTo(AnnualPlan::class);
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function actualEvents()
    {
        return $this->hasMany(ActualEvent::class);
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->isDirector() || $user->isAnalyst()) {
            return $query;
        }

        if ($user->isDepartmentHead()) {
            return $query->whereHas('annualPlan', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        return $query->whereNull('id');
    }
}