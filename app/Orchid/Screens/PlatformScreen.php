<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\ActualEvent;
use App\Models\AnnualPlan;
use App\Models\Department;
use App\Models\PlannedEvent;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

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

        $plansQuery = AnnualPlan::query()
            ->forUser($user)
            ->where('year', $currentYear);

        $plannedEventsQuery = PlannedEvent::query()
            ->whereHas('annualPlan', function ($query) use ($user, $currentYear) {
                $query->where('year', $currentYear);

                if ($user->isDepartmentHead()) {
                    $query->where('department_id', $user->department_id);
                }
            });

        $completedPlannedEventsQuery = (clone $plannedEventsQuery)
            ->whereHas('actualEvents', function ($query) use ($currentYear) {
                $query->where('status', ActualEvent::STATUS_ARCHIVED)
                    ->whereYear('actual_start_at', $currentYear);
            });

        $participantsQuery = DB::table('actual_event_participants')
            ->join('actual_events', 'actual_events.id', '=', 'actual_event_participants.actual_event_id');

        if ($user->isDepartmentHead()) {
            $participantsQuery->where('actual_events.department_id', $user->department_id);
        }

        $totalPlannedEvents = (clone $plannedEventsQuery)->count();
        $completedEvents = (clone $completedPlannedEventsQuery)->count();
        $totalPlans = (clone $plansQuery)->count();
        $totalParticipants = (clone $participantsQuery)->count();

        $departmentProgress = [];
        if ($user->isDirector() || $user->isAnalyst()) {
            $departmentProgress = Department::query()
                ->orderBy('name')
                ->get()
                ->map(function (Department $department) use ($currentYear) {
                    $plannedCount = PlannedEvent::query()
                        ->whereHas('annualPlan', function ($query) use ($department, $currentYear) {
                            $query->where('department_id', $department->id)
                                ->where('year', $currentYear);
                        })
                        ->count();

                    $completedCount = PlannedEvent::query()
                        ->whereHas('annualPlan', function ($query) use ($department, $currentYear) {
                            $query->where('department_id', $department->id)
                                ->where('year', $currentYear);
                        })
                        ->whereHas('actualEvents', function ($query) use ($currentYear) {
                            $query->where('status', ActualEvent::STATUS_ARCHIVED)
                                ->whereYear('actual_start_at', $currentYear);
                        })
                        ->count();

                    return [
                        'name' => $department->display_name,
                        'total' => $plannedCount,
                        'completed' => $completedCount,
                        'percentage' => $plannedCount > 0
                            ? min(100, round(($completedCount / $plannedCount) * 100))
                            : 0,
                    ];
                });
        }

        $chartData = $this->getChartData($user);

        return [
            'totalPlannedEvents' => $totalPlannedEvents,
            'completedEvents' => $completedEvents,
            'totalPlans' => $totalPlans,
            'totalParticipants' => $totalParticipants,
            'departmentProgress' => $departmentProgress,
            'currentYear' => $currentYear,
            'chartData' => $chartData,
        ];
    }

    private function getChartData($user): array
    {
        $days = 30;
        $dates = [];
        $eventsData = [];
        $participantsData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = now()->subDays($i)->format('d.m');

            $eventsQuery = ActualEvent::whereDate('actual_start_at', $date);
            if ($user->isDepartmentHead()) {
                $eventsQuery->where('department_id', $user->department_id);
            }
            $eventsData[] = $eventsQuery->count();

            $participantsQuery = DB::table('actual_event_participants')
                ->join('actual_events', 'actual_events.id', '=', 'actual_event_participants.actual_event_id')
                ->whereDate('actual_events.actual_start_at', $date);
            if ($user->isDepartmentHead()) {
                $participantsQuery->where('actual_events.department_id', $user->department_id);
            }
            $participantsData[] = $participantsQuery->count();
        }

        return [
            'dates' => $dates,
            'events' => $eventsData,
            'participants' => $participantsData,
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
