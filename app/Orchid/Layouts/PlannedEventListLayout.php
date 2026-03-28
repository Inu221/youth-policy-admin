<?php

namespace App\Orchid\Layouts;

use App\Models\PlannedEvent;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class PlannedEventListLayout extends Table
{
    protected $target = 'plannedEvents';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')
                ->sort(),

            TD::make('title', 'Мероприятие')
                ->sort()
                ->render(function (PlannedEvent $plannedEvent) {
                    return Link::make($plannedEvent->title)
                        ->route('platform.planned-events.edit', $plannedEvent);
                }),

            TD::make('annual_plan', 'План')
                ->render(function (PlannedEvent $plannedEvent) {
                    return $plannedEvent->annualPlan?->title ?? '—';
                }),

            TD::make('department', 'Подразделение')
                ->render(function (PlannedEvent $plannedEvent) {
                    return $plannedEvent->annualPlan?->department?->display_name ?? '—';
                }),

            TD::make('planned_start_at', 'Дата начала')
                ->sort()
                ->render(function (PlannedEvent $plannedEvent) {
                    return $plannedEvent->planned_start_at?->format('d.m.Y H:i') ?? '—';
                }),

            TD::make('responsible_user', 'Ответственный')
                ->render(function (PlannedEvent $plannedEvent) {
                    return $plannedEvent->responsibleUser?->full_name ?? '—';
                }),

            TD::make('planned_participants_count', 'План. участники')
                ->sort()
                ->render(function (PlannedEvent $plannedEvent) {
                    return $plannedEvent->planned_participants_count ?? '—';
                }),

            TD::make('status', 'Статус')
                ->sort()
                ->render(function (PlannedEvent $plannedEvent) {
                    return match ($plannedEvent->status) {
                        PlannedEvent::STATUS_PLANNED => 'Запланировано',
                        PlannedEvent::STATUS_IN_PROGRESS => 'Проводится',
                        PlannedEvent::STATUS_ARCHIVED => 'Архив',
                        PlannedEvent::STATUS_CANCELLED => 'Отменено',
                        default => $plannedEvent->status,
                    };
                }),

            TD::make('updated_at', 'Обновлено')
                ->sort()
                ->render(function (PlannedEvent $plannedEvent) {
                    return $plannedEvent->updated_at?->format('d.m.Y H:i') ?? '—';
                }),
        ];
    }
}