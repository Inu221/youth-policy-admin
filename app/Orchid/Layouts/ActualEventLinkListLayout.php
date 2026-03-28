<?php

namespace App\Orchid\Layouts;

use App\Models\ActualEventLink;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ActualEventLinkListLayout extends Table
{
    protected $target = 'actualEventLinks';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')
                ->sort(),

            TD::make('actual_event', 'Мероприятие')
                ->render(function (ActualEventLink $link) {
                    return $link->actualEvent?->title ?? '—';
                }),

            TD::make('link_type', 'Тип')
                ->sort()
                ->render(function (ActualEventLink $link) {
                    return match ($link->link_type) {
                        ActualEventLink::TYPE_SOCIAL_POST => 'Пост в соцсети',
                        ActualEventLink::TYPE_MEDIA => 'Медиа',
                        ActualEventLink::TYPE_OTHER => 'Другое',
                        default => $link->link_type,
                    };
                }),

            TD::make('url', 'Ссылка')
                ->sort()
                ->render(function (ActualEventLink $link) {
                    return Link::make('Открыть')
                        ->href($link->url)
                        ->target('_blank');
                }),

            TD::make('is_primary', 'Основная')
                ->sort()
                ->render(fn (ActualEventLink $link) => $link->is_primary ? 'Да' : 'Нет'),

            TD::make('created_by', 'Добавил')
                ->render(function (ActualEventLink $link) {
                    return $link->creator?->full_name ?? '—';
                }),

            TD::make('edit', 'Действие')
                ->render(function (ActualEventLink $link) {
                    return Link::make('Редактировать')
                        ->route('platform.actual-event-links.edit', $link);
                }),
        ];
    }
}