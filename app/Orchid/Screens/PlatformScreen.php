<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use App\Models\ActualEvent;
use App\Models\AnnualPlan;
use App\Models\Department;
use App\Models\Participant;

class PlatformScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $user = auth()->user();
        $currentYear = now()->year;

        // Get counts filtered by user role
        $eventsQuery = ActualEvent::query();
        $plansQuery = AnnualPlan::query();

        if ($user->isDepartmentHead()) {
            $eventsQuery->where('department_id', $user->department_id);
            $plansQuery->where('department_id', $user->department_id);
        }

        // Overall stats
        $totalActualEvents = (clone $eventsQuery)->count();
        $completedEvents = (clone $eventsQuery)->where('status', ActualEvent::STATUS_ARCHIVED)->count();
        $totalPlans = (clone $plansQuery)->where('year', $currentYear)->count();
        $totalParticipants = Participant::sum('attendance_count');

        // Department progress (for director only)
        $departmentProgress = [];
        if ($user->isDirector() || $user->isAnalyst()) {
            $departmentProgress = Department::with(['actualEvents' => function ($q) use ($currentYear) {
                $q->whereYear('actual_start_at', $currentYear);
            }])
                ->withCount([
                    'actualEvents as completed_count' => function ($q) use ($currentYear) {
                        $q->whereYear('actual_start_at', $currentYear)
                          ->where('status', ActualEvent::STATUS_ARCHIVED);
                    },
                    'actualEvents as total_count' => function ($q) use ($currentYear) {
                        $q->whereYear('actual_start_at', $currentYear);
                    }
                ])
                ->get()
                ->map(function ($dept) {
                    return [
                        'name' => $dept->short_name ?: $dept->name,
                        'total' => $dept->total_count,
                        'completed' => $dept->completed_count,
                        'percentage' => $dept->total_count > 0 
                            ? round(($dept->completed_count / $dept->total_count) * 100) 
                            : 0,
                    ];
                });
        }

        return [
            'totalActualEvents' => $totalActualEvents,
            'completedEvents' => $completedEvents,
            'totalPlans' => $totalPlans,
            'totalParticipants' => $totalParticipants,
            'departmentProgress' => $departmentProgress,
            'currentYear' => $currentYear,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Панель управления';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Обзор системы учета мероприятий молодежной политики';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('platform.dashboard'),
        ];
    }
}
