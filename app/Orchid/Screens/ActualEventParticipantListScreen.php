<?php

namespace App\Orchid\Screens;

use App\Models\ActualEventParticipant;
use App\Orchid\Layouts\ActualEventParticipantListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class ActualEventParticipantListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'actualEventParticipants' => ActualEventParticipant::with([
                'actualEvent',
                'participant',
                'addedBy',
            ])
                ->orderByDesc('id')
                ->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return 'Участники мероприятий';
    }

    public function description(): ?string
    {
        return 'Привязка участников к фактическим мероприятиям';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Добавить участника к мероприятию')
                ->icon('bs.plus-circle')
                ->route('platform.actual-event-participants.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            ActualEventParticipantListLayout::class,
        ];
    }
}