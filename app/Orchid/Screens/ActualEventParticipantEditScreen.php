<?php

namespace App\Orchid\Screens;

use App\Models\ActualEvent;
use App\Models\ActualEventParticipant;
use App\Models\Participant;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ActualEventParticipantEditScreen extends Screen
{
    public ?ActualEventParticipant $actualEventParticipant = null;

    public function query(ActualEventParticipant $actualEventParticipant): iterable
    {
        return [
            'actualEventParticipant' => $actualEventParticipant,
        ];
    }

    public function name(): ?string
    {
        return $this->actualEventParticipant?->exists
            ? 'Редактирование привязки участника'
            : 'Добавление участника к мероприятию';
    }

    public function description(): ?string
    {
        return 'Связь участника с фактическим мероприятием';
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
                ->canSee($this->actualEventParticipant?->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Relation::make('actualEventParticipant.actual_event_id')
                    ->title('Фактическое мероприятие')
                    ->fromModel(ActualEvent::class, 'title')
                    ->required(),

                Relation::make('actualEventParticipant.participant_id')
                    ->title('Участник')
                    ->fromModel(Participant::class, 'full_name')
                    ->required(),
            ]),
        ];
    }

    public function save(ActualEventParticipant $actualEventParticipant, Request $request)
    {
        $validated = $request->validate([
            'actualEventParticipant.actual_event_id' => ['required', 'integer', 'exists:actual_events,id'],
            'actualEventParticipant.participant_id' => ['required', 'integer', 'exists:participants,id'],
        ]);

        $data = $validated['actualEventParticipant'];

        try {
            $isNew = ! $actualEventParticipant->exists;
            $oldParticipantId = $actualEventParticipant->participant_id;

            $data['added_by'] = $actualEventParticipant->exists
                ? $actualEventParticipant->added_by
                : auth()->id();

            if (! $actualEventParticipant->exists) {
                $data['created_at'] = now();
            }

            $actualEventParticipant->fill($data)->save();

            if ($isNew) {
                $participant = Participant::find($data['participant_id']);
                if ($participant) {
                    $participant->increment('attendance_count');
                }
            } elseif ($oldParticipantId && $oldParticipantId != $data['participant_id']) {
                $oldParticipant = Participant::find($oldParticipantId);
                if ($oldParticipant && $oldParticipant->attendance_count > 0) {
                    $oldParticipant->decrement('attendance_count');
                }

                $newParticipant = Participant::find($data['participant_id']);
                if ($newParticipant) {
                    $newParticipant->increment('attendance_count');
                }
            }

            Alert::info('Участник привязан к мероприятию.');

            return redirect()->route('platform.actual-event-participants');
        } catch (QueryException $e) {
            Alert::error('Такая привязка уже существует.');

            return back();
        }
    }

    public function remove(ActualEventParticipant $actualEventParticipant)
    {
        $participantId = $actualEventParticipant->participant_id;

        $actualEventParticipant->delete();

        $participant = Participant::find($participantId);
        if ($participant && $participant->attendance_count > 0) {
            $participant->decrement('attendance_count');
        }

        Alert::info('Привязка удалена.');

        return redirect()->route('platform.actual-event-participants');
    }
}