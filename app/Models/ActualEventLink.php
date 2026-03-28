<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;

class ActualEventLink extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;

    public const TYPE_SOCIAL_POST = 'social_post';
    public const TYPE_MEDIA = 'media';
    public const TYPE_OTHER = 'other';

    public $timestamps = false;

    protected $fillable = [
        'actual_event_id',
        'link_type',
        'url',
        'is_primary',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected $allowedSorts = [
        'id',
        'actual_event_id',
        'link_type',
        'url',
        'is_primary',
        'created_by',
        'created_at',
    ];

    public function actualEvent()
    {
        return $this->belongsTo(ActualEvent::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}