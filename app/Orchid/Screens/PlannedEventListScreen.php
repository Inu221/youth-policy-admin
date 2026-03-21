<?php

namespace App\Orchid\Screens;

use App\Models\PlannedEvent;
use App\Orchid\Layouts\PlannedEventListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class PlannedEventListScreen extends Screen
{
    public function query(): iterable
    {
        $user = auth()->user();

        return [
            'plannedEvents' => PlannedEvent::with(['annualPlan.department', 'responsibleUser'])
                ->forUser($user)
                ->orderByDesc('planned_start_at')
                ->orderByDesc('id')
                ->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Плановые мероприятия';
    }

    public function description(): ?string
    {
        return 'Мероприятия, входящие в годовые планы';
    }

    public function commandBar(): iterable
    {
        $user = auth()->user();

        return [
            Link::make('Создать мероприятие')
                ->icon('bs.plus-circle')
                ->route('platform.planned-events.create')
                ->canSee($user->can('create', PlannedEvent::class)),
        ];
    }

    public function layout(): iterable
    {
        return [
            PlannedEventListLayout::class,
        ];
    }
}
