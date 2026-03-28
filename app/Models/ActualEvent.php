<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;

class ActualEvent extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AsSource;
    use Filterable;

    public const STATUS_PLANNED = 'planned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'department_id',
        'planned_event_id',
        'title',
        'description',
        'actual_start_at',
        'actual_end_at',
        'location_name',
        'location_url',
        'responsible_user_id',
        'planned_participants_snapshot',
        'actual_participants_count',
        'status',
        'created_by',
        'updated_by',
        'completed_at',
    ];

    protected $casts = [
        'actual_start_at' => 'datetime',
        'actual_end_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $allowedSorts = [
        'id',
        'department_id',
        'planned_event_id',
        'title',
        'actual_start_at',
        'responsible_user_id',
        'actual_participants_count',
        'status',
        'updated_at',
        'created_at',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function plannedEvent()
    {
        return $this->belongsTo(PlannedEvent::class);
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

    public function links()
    {
        return $this->hasMany(ActualEventLink::class);
    }

    public function files()
    {
        return $this->hasMany(ActualEventFile::class);
    }

    public function verification()
    {
        return $this->hasOne(ActualEventVerification::class);
    }

    public function participants()
    {
        return $this->belongsToMany(
            Participant::class,
            'actual_event_participants'
        )->withPivot('added_by', 'created_at');
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->isDirector() || $user->isAnalyst()) {
            return $query;
        }

        if ($user->isDepartmentHead()) {
            return $query->where('department_id', $user->department_id);
        }

        return $query->whereNull('id');
    }
}