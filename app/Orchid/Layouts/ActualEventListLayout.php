<?php

namespace App\Orchid\Layouts;

use App\Models\ActualEvent;
use App\Models\ActualEventVerification;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ActualEventListLayout extends Table
{
    protected $target = 'actualEvents';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')
                ->sort(),

            TD::make('title', 'Мероприятие')
                ->sort()
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
                ->sort()
                ->render(function (ActualEvent $actualEvent) {
                    return $actualEvent->actual_start_at?->format('d.m.Y H:i') ?? '—';
                }),

            TD::make('responsible_user', 'Ответственный')
                ->render(function (ActualEvent $actualEvent) {
                    return $actualEvent->responsibleUser?->full_name ?? '—';
                }),

            TD::make('actual_participants_count', 'Факт. участники')
                ->sort()
                ->render(function (ActualEvent $actualEvent) {
                    return $actualEvent->actual_participants_count;
                }),

            TD::make('social_link', 'Ссылка на соцсеть')
                ->render(function (ActualEvent $actualEvent) {
                    $primaryLink = $actualEvent->links()->where('link_type', 'social_post')->where('is_primary', true)->first();
                    if ($primaryLink) {
                        return '<a href="' . e($primaryLink->url) . '" target="_blank" class="text-primary" title="' . e($primaryLink->url) . '">
                            <i class="bi bi-link-45deg"></i> Открыть
                        </a>';
                    }
                    return '<span class="text-muted">—</span>';
                }),

            TD::make('verification', 'Верификация')
                ->render(function (ActualEvent $actualEvent) {
                    $verification = $actualEvent->verification;
                    if (!$verification) {
                        return '<span class="badge bg-secondary">Не проверено</span>';
                    }

                    return match ($verification->status) {
                        ActualEventVerification::STATUS_APPROVED => '<span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Одобрено</span>',
                        ActualEventVerification::STATUS_REJECTED => '<span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Отклонено</span>',
                        default => '<span class="badge bg-warning">Ожидает проверки</span>',
                    };
                })
                ->canSee(auth()->user() && (auth()->user()->isAnalyst() || auth()->user()->isDirector())),

            TD::make('status', 'Статус')
                ->sort()
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
                ->sort()
                ->render(function (ActualEvent $actualEvent) {
                    return $actualEvent->updated_at?->format('d.m.Y H:i') ?? '—';
                }),
        ];
    }
}