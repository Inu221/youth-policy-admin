<?php

namespace App\Orchid\Screens;

use App\Models\Participant;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ParticipantEditScreen extends Screen
{
    public ?Participant $participant = null;

    public function query(Participant $participant): iterable
    {
        return [
            'participant' => $participant,
        ];
    }

    public function name(): ?string
    {
        return $this->participant?->exists
            ? 'Редактирование участника'
            : 'Создание участника';
    }

    public function description(): ?string
    {
        return 'Карточка участника';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Сохранить')
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make('Удалить')
                ->icon('bs.trash3')
                ->method('remove')
                ->canSee($this->participant?->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('participant.full_name')
                    ->title('ФИО')
                    ->required(),

                DateTimer::make('participant.birth_date')
                    ->title('Дата рождения')
                    ->format('Y-m-d')
                    ->allowInput(),

                Input::make('participant.phone')
                    ->title('Телефон'),

                Input::make('participant.email')
                    ->title('Email')
                    ->type('email'),

                Input::make('participant.attendance_count')
                    ->title('Количество посещений')
                    ->type('number')
                    ->help('Пока редактируется вручную. Позже сделаем автоматический пересчет.')
                    ->required(),
            ]),
        ];
    }

    public function save(Participant $participant, Request $request)
    {
        $validated = $request->validate([
            'participant.full_name' => ['required', 'string', 'max:255'],
            'participant.birth_date' => ['nullable', 'date'],
            'participant.phone' => ['nullable', 'string', 'max:50'],
            'participant.email' => ['nullable', 'email', 'max:255'],
            'participant.attendance_count' => ['required', 'integer', 'min:0'],
        ]);

        $participant->fill($validated['participant'])->save();

        Alert::info('Участник сохранен.');

        return redirect()->route('platform.participants');
    }

    public function remove(Participant $participant)
    {
        $participant->delete();

        Alert::info('Участник удален.');

        return redirect()->route('platform.participants');
    }
}