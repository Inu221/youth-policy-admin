<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;
use Orchid\Filters\Filterable;

class ActualEventFile extends Model
{
    use HasFactory;
    use AsSource;
    use Filterable;

    public $timestamps = false;

    protected $fillable = [
        'actual_event_id',
        'stored_name',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'uploaded_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $allowedSorts = [
        'id',
        'actual_event_id',
        'original_name',
        'file_size',
        'uploaded_by',
        'created_at',
    ];

    public function actualEvent()
    {
        return $this->belongsTo(ActualEvent::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}