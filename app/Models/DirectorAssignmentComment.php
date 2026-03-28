<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Orchid\Screen\AsSource;

class DirectorAssignmentComment extends Model
{
    use HasFactory;
    use SoftDeletes;
    use AsSource;

    protected $fillable = [
        'director_assignment_id',
        'comment',
        'user_id',
    ];

    public function directorAssignment()
    {
        return $this->belongsTo(DirectorAssignment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
