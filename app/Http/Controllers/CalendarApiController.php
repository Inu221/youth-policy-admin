<?php

namespace App\Http\Controllers;

use App\Models\ActualEvent;
use Illuminate\Http\Request;

class CalendarApiController extends Controller
{
    public function events(Request $request)
    {
        $user = auth()->user();
        $start = $request->input('start');
        $end = $request->input('end');

        $query = ActualEvent::query()
            ->with(['department', 'responsibleUser'])
            ->forUser($user);

        if ($start) {
            $query->where('actual_start_at', '>=', $start);
        }

        if ($end) {
            $query->where('actual_start_at', '<=', $end);
        }

        $events = $query->get()->map(function ($event) {
            $title = $event->title;

            // Add department name if available
            if ($event->department) {
                $title = '[' . ($event->department->short_name ?? $event->department->name) . '] ' . $title;
            }

            // Color by status
            $color = match ($event->status) {
                ActualEvent::STATUS_ARCHIVED => '#198754', // green
                ActualEvent::STATUS_IN_PROGRESS => '#0d6efd', // blue
                ActualEvent::STATUS_PLANNED => '#6c757d', // gray
                ActualEvent::STATUS_CANCELLED => '#dc3545', // red
                default => '#0d6efd',
            };

            return [
                'id' => $event->id,
                'title' => $title,
                'start' => $event->actual_start_at->toIso8601String(),
                'end' => $event->actual_end_at?->toIso8601String(),
                'color' => $color,
                'extendedProps' => [
                    'department' => $event->department?->name,
                    'responsible' => $event->responsibleUser?->full_name,
                    'participants_count' => $event->actual_participants_count,
                    'status' => $event->status,
                ],
            ];
        });

        return response()->json($events);
    }
}
