<?php

namespace App\Orchid\Screens;

use App\Models\ActualEventLink;
use App\Orchid\Layouts\ActualEventLinkListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class ActualEventLinkListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'actualEventLinks' => ActualEventLink::with(['actualEvent', 'creator'])
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return 'Ссылки мероприятий';
    }

    public function description(): ?string
    {
        return 'Ссылки-подтверждения для фактических мероприятий';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Добавить ссылку')
                ->icon('bs.plus-circle')
                ->route('platform.actual-event-links.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            ActualEventLinkListLayout::class,
        ];
    }
}