<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;

class Participant extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AsSource;
    use Filterable;

    protected $fillable = [
        'full_name',
        'birth_date',
        'phone',
        'email',
        'attendance_count',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    protected $allowedSorts = [
        'id',
        'full_name',
        'birth_date',
        'phone',
        'email',
        'attendance_count',
        'updated_at',
        'created_at',
    ];

    public function actualEvents()
    {
        return $this->belongsToMany(
            ActualEvent::class,
            'actual_event_participants'
        )->withPivot('added_by', 'created_at');
    }
}