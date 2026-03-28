<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;

class Department extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AsSource;
    use Filterable;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'short_name',
        'responsible_user_id',
        'status',
    ];

    protected $allowedSorts = [
        'id',
        'name',
        'short_name',
        'responsible_user_id',
        'status',
        'updated_at',
        'created_at',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function annualPlans()
    {
        return $this->hasMany(AnnualPlan::class);
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
            return $query->where('id', $user->department_id);
        }

        return $query->whereNull('id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->short_name ?: $this->name;
    }
}