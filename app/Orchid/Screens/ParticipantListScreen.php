<?php

namespace App\Orchid\Screens;

use App\Models\Participant;
use App\Orchid\Layouts\ParticipantListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class ParticipantListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'participants' => Participant::filters()
                ->defaultSort('attendance_count', 'desc')
                ->orderBy('full_name')
                ->paginate(20),
        ];
    }

    public function name(): ?string
    {
        return 'Участники';
    }

    public function description(): ?string
    {
        return 'База активных участников мероприятий';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Добавить участника')
                ->icon('bs.person-plus')
                ->route('platform.participants.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            ParticipantListLayout::class,
        ];
    }
}