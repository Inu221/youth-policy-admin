<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;

class AnnualPlan extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AsSource;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'department_id',
        'year',
        'title',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'approval_comment',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function plannedEvents()
    {
        return $this->hasMany(PlannedEvent::class);
    }
}