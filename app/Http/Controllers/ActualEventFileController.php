<?php

namespace App\Http\Controllers;

use App\Models\ActualEventFile;
use Illuminate\Support\Facades\Storage;

class ActualEventFileController extends Controller
{
    public function download(ActualEventFile $file)
    {
        $user = auth()->user();

        // Check access
        if ($user->isDepartmentHead()) {
            abort_unless($file->actualEvent->department_id === $user->department_id, 403);
        }

        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }
}
