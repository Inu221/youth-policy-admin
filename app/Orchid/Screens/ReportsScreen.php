<?php

namespace App\Orchid\Screens;

use App\Models\ActualEvent;
use App\Models\Department;
use App\Models\Participant;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class ReportsScreen extends Screen
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

        // Top 20 participants by attendance
        $topParticipants = Participant::select('full_name', 'attendance_count', 'birth_date')
            ->orderByDesc('attendance_count')
            ->limit(20)
            ->get();

        // Event status statistics
        $eventsByStatus = ActualEvent::select('status', DB::raw('count(*) as count'))
            ->when($user->isDepartmentHead(), function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            })
            ->whereYear('actual_start_at', $currentYear)
            ->groupBy('status')
            ->get();

        // Participant statistics by department
        $participantsByDepartment = [];
        if ($user->isDirector() || $user->isAnalyst()) {
            $participantsByDepartment = Department::withCount([
                'actualEvents as total_participants' => function ($q) use ($currentYear) {
                    $q->whereYear('actual_start_at', $currentYear)
                        ->select(DB::raw('COALESCE(SUM(actual_participants_count), 0)'));
                }
            ])->get();
        }

        // Age distribution (approximate)
        $ageDistribution = Participant::select(
            DB::raw('CASE 
                WHEN YEAR(CURDATE()) - YEAR(birth_date) < 18 THEN "До 18"
                WHEN YEAR(CURDATE()) - YEAR(birth_date) BETWEEN 18 AND 25 THEN "18-25"
                WHEN YEAR(CURDATE()) - YEAR(birth_date) BETWEEN 26 AND 35 THEN "26-35"
                ELSE "36+"
            END as age_group'),
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('birth_date')
            ->groupBy('age_group')
            ->get();

        return [
            'topParticipants' => $topParticipants,
            'eventsByStatus' => $eventsByStatus,
            'participantsByDepartment' => $participantsByDepartment,
            'ageDistribution' => $ageDistribution,
            'currentYear' => $currentYear,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Отчеты и аналитика';
    }

    public function description(): ?string
    {
        return 'Статистика по мероприятиям и участникам';
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
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('platform.reports'),
        ];
    }
}
