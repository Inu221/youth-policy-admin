<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;

class Department extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AsSource;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name',
        'short_name',
        'responsible_user_id',
        'status',
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

    public function getDisplayNameAttribute(): string
    {
        return $this->short_name ?: $this->name;
    }
}