<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\ActualEvent;
use App\Models\Department;
use App\Models\PlannedEvent;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class DepartmentRankingScreen extends Screen
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

        // Проверка прав доступа (только director и analyst)
        if (!$user->isDirector() && !$user->isAnalyst()) {
            abort(403, 'Доступ запрещен');
        }

        $departmentRankings = Department::query()
            ->where('status', Department::STATUS_ACTIVE)
            ->orderBy('name')
            ->get()
            ->map(function (Department $department) use ($currentYear) {
                // Логика из PlatformScreen.php (строки 64-90)
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

                $percentage = $plannedCount > 0
                    ? min(100, round(($completedCount / $plannedCount) * 100))
                    : 0;

                return [
                    'department_id' => $department->id,
                    'name' => $department->display_name,
                    'total' => $plannedCount,
                    'completed' => $completedCount,
                    'percentage' => $percentage,
                    'department' => $department,
                ];
            })
            // Сортировка по проценту выполнения (по убыванию)
            ->sortByDesc('percentage')
            ->values();

        return [
            'departmentRankings' => $departmentRankings,
            'currentYear' => $currentYear,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Рейтинг муниципалитетов';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Рейтинг подразделений по выполнению планов мероприятий';
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
            Layout::view('platform.department-ranking'),
        ];
    }
}
