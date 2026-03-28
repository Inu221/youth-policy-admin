<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;

class DirectorAssignment extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AsSource;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'department_id',
        'title',
        'description',
        'status',
        'due_date',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(DirectorAssignmentComment::class)
            ->orderBy('created_at', 'desc');
    }

    public function scopeForUser($query, User $user)
    {
        if ($user->isDirector()) {
            return $query; // Видит все поручения
        }

        if ($user->isDepartmentHead()) {
            return $query->where('department_id', $user->department_id);
        }

        // Аналитик не имеет доступа
        return $query->whereNull('id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Ожидает',
            self::STATUS_IN_PROGRESS => 'В работе',
            self::STATUS_COMPLETED => 'Выполнено',
            default => $this->status,
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === self::STATUS_COMPLETED || !$this->due_date) {
            return false;
        }

        return $this->due_date->isPast();
    }
}
