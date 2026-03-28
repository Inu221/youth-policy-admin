<?php

namespace App\Orchid\Screens;

use App\Models\ActualEvent;
use App\Orchid\Layouts\ActualEventListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class ActualEventListScreen extends Screen
{
    public function query(): iterable
    {
        $user = auth()->user();

        return [
            'actualEvents' => ActualEvent::with([
                'department',
                'plannedEvent.annualPlan',
                'responsibleUser',
            ])
                ->forUser($user)
                ->filters()
                ->defaultSort('actual_start_at', 'desc')
                ->orderByDesc('id')
                ->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Фактические мероприятия';
    }

    public function description(): ?string
    {
        return 'Реальное исполнение мероприятий';
    }

    public function commandBar(): iterable
    {
        $user = auth()->user();

        return [
            Link::make('Создать фактическое мероприятие')
                ->icon('bs.plus-circle')
                ->route('platform.actual-events.create')
                ->canSee($user->isDirector() || $user->isDepartmentHead()),
        ];
    }

    public function layout(): iterable
    {
        return [
            ActualEventListLayout::class,
        ];
    }
}