<?php

namespace App\Orchid\Layouts;

use App\Models\Participant;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ParticipantListLayout extends Table
{
    protected $target = 'participants';

    protected function columns(): iterable
    {
        $user = auth()->user();
        $canViewContact = $user && ($user->isDirector() || $user->isDepartmentHead());

        return [
            TD::make('id', 'ID')
                ->sort(),

            TD::make('full_name', 'ФИО')
                ->sort()
                ->render(function (Participant $participant) {
                    return Link::make($participant->full_name)
                        ->route('platform.participants.edit', $participant);
                }),

            TD::make('birth_date', 'Дата рождения')
                ->sort()
                ->render(function (Participant $participant) {
                    return $participant->birth_date?->format('d.m.Y') ?? '—';
                }),

            TD::make('phone', 'Телефон')
                ->sort()
                ->render(fn (Participant $participant) => $participant->phone ?: '—')
                ->canSee($canViewContact),

            TD::make('email', 'Email')
                ->sort()
                ->render(fn (Participant $participant) => $participant->email ?: '—')
                ->canSee($canViewContact),

            TD::make('attendance_count', 'Посещений')
                ->sort()
                ->render(fn (Participant $participant) => $participant->attendance_count),

            TD::make('updated_at', 'Обновлено')
                ->sort()
                ->render(function (Participant $participant) {
                    return $participant->updated_at?->format('d.m.Y H:i') ?? '—';
                }),
        ];
    }
}