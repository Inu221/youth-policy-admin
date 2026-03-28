<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;

class ActualEventParticipant extends Model
{
    use AsSource;
    use Filterable;

    public $timestamps = false;

    protected $table = 'actual_event_participants';

    protected $fillable = [
        'actual_event_id',
        'participant_id',
        'added_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $allowedSorts = [
        'id',
        'actual_event_id',
        'participant_id',
        'added_by',
        'created_at',
    ];

    public function actualEvent()
    {
        return $this->belongsTo(ActualEvent::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}