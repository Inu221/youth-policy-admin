<?php

namespace App\Orchid\Layouts;

use App\Models\ActualEventParticipant;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ActualEventParticipantListLayout extends Table
{
    protected $target = 'actualEventParticipants';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),

            TD::make('actual_event', 'Мероприятие')
                ->render(function (ActualEventParticipant $item) {
                    return $item->actualEvent?->title ?? '—';
                }),

            TD::make('participant', 'Участник')
                ->render(function (ActualEventParticipant $item) {
                    return $item->participant?->full_name ?? '—';
                }),

            TD::make('added_by', 'Добавил')
                ->render(function (ActualEventParticipant $item) {
                    return $item->addedBy?->full_name ?? '—';
                }),

            TD::make('created_at', 'Дата привязки')
                ->render(function (ActualEventParticipant $item) {
                    return $item->created_at?->format('d.m.Y H:i') ?? '—';
                }),

            TD::make('edit', 'Действие')
                ->render(function (ActualEventParticipant $item) {
                    return Link::make('Редактировать')
                        ->route('platform.actual-event-participants.edit', $item);
                }),
        ];
    }
}