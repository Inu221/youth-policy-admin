<?php

namespace App\Orchid\Layouts;

use App\Models\ActualEvent;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ActualEventListLayout extends Table
{
    protected $target = 'actualEvents';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),

            TD::make('title', 'Мероприятие')
                ->render(function (ActualEvent $actualEvent) {
                    return Link::make($actualEvent->title)
                        ->route('platform.actual-events.edit', $actualEvent);
                }),

            TD::make('department', 'Подразделение')
                ->render(function (ActualEvent $actualEvent) {
                    return $actualEvent->department?->display_name ?? '—';
                }),

            TD::make('planned_event', 'Плановое мероприятие')
                ->render(function (ActualEvent $actualEvent) {
                    return $actualEvent->plannedEvent?->title ?? 'Внеплановое';
                }),

            TD::make('actual_start_at', 'Дата начала')
                ->render(function (ActualEvent $actualEvent) {
                    return $actualEvent->actual_start_at?->format('d.m.Y H:i') ?? '—';
                }),

            TD::make('responsible_user', 'Ответственный')
                ->render(function (ActualEvent $actualEvent) {
                    return $actualEvent->responsibleUser?->full_name ?? '—';
                }),

            TD::make('actual_participants_count', 'Факт. участники')
                ->render(function (ActualEvent $actualEvent) {
                    return $actualEvent->actual_participants_count;
                }),

            TD::make('status', 'Статус')
                ->render(function (ActualEvent $actualEvent) {
                    return match ($actualEvent->status) {
                        ActualEvent::STATUS_PLANNED => 'Запланировано',
                        ActualEvent::STATUS_IN_PROGRESS => 'Проводится',
                        ActualEvent::STATUS_ARCHIVED => 'Архив',
                        ActualEvent::STATUS_CANCELLED => 'Отменено',
                        default => $actualEvent->status,
                    };
                }),

            TD::make('updated_at', 'Обновлено')
                ->render(function (ActualEvent $actualEvent) {
                    return $actualEvent->updated_at?->format('d.m.Y H:i') ?? '—';
                }),
        ];
    }
}