<?php

namespace App\Orchid\Screens;

use App\Models\AnnualPlan;
use App\Models\PlannedEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PlannedEventEditScreen extends Screen
{
    public ?PlannedEvent $plannedEvent = null;

    public function query(PlannedEvent $plannedEvent): iterable
    {
        return [
            'plannedEvent' => $plannedEvent,
        ];
    }

    public function name(): ?string
    {
        return $this->plannedEvent?->exists
            ? 'Редактирование планового мероприятия'
            : 'Создание планового мероприятия';
    }

    public function description(): ?string
    {
        return 'Карточка планового мероприятия';
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
                ->canSee($this->plannedEvent?->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Relation::make('plannedEvent.annual_plan_id')
                    ->title('Годовой план')
                    ->fromModel(AnnualPlan::class, 'title')
                    ->required(),

                Input::make('plannedEvent.title')
                    ->title('Название мероприятия')
                    ->required(),

                Input::make('plannedEvent.description')
                    ->title('Описание'),

                DateTimer::make('plannedEvent.planned_start_at')
                    ->title('Дата и время начала')
                    ->enableTime()
                    ->required(),

                DateTimer::make('plannedEvent.planned_end_at')
                    ->title('Дата и время окончания')
                    ->enableTime(),

                Input::make('plannedEvent.location_name')
                    ->title('Место проведения'),

                Input::make('plannedEvent.location_url')
                    ->title('Ссылка на место / гео-ссылка'),

                Relation::make('plannedEvent.responsible_user_id')
                    ->title('Ответственный')
                    ->fromModel(User::class, 'full_name')
                    ->required(),

                Input::make('plannedEvent.planned_participants_count')
                    ->title('Плановое количество участников')
                    ->type('number'),

                Select::make('plannedEvent.status')
                    ->title('Статус')
                    ->options([
                        PlannedEvent::STATUS_PLANNED => 'Запланировано',
                        PlannedEvent::STATUS_IN_PROGRESS => 'Проводится',
                        PlannedEvent::STATUS_ARCHIVED => 'Архив',
                        PlannedEvent::STATUS_CANCELLED => 'Отменено',
                    ])
                    ->required(),
            ]),
        ];
    }

    public function save(PlannedEvent $plannedEvent, Request $request)
    {
        $validated = $request->validate([
            'plannedEvent.annual_plan_id' => ['required', 'integer', 'exists:annual_plans,id'],
            'plannedEvent.title' => ['required', 'string', 'max:255'],
            'plannedEvent.description' => ['nullable', 'string'],
            'plannedEvent.planned_start_at' => ['required', 'date'],
            'plannedEvent.planned_end_at' => ['nullable', 'date', 'after_or_equal:plannedEvent.planned_start_at'],
            'plannedEvent.location_name' => ['nullable', 'string', 'max:255'],
            'plannedEvent.location_url' => ['nullable', 'string', 'max:1000'],
            'plannedEvent.responsible_user_id' => ['required', 'integer', 'exists:users,id'],
            'plannedEvent.planned_participants_count' => ['nullable', 'integer', 'min:0'],
            'plannedEvent.status' => ['required', 'in:planned,in_progress,archived,cancelled'],
        ]);

        $data = $validated['plannedEvent'];

        if (! $plannedEvent->exists) {
            $data['created_by'] = auth()->id();
        } else {
            $data['updated_by'] = auth()->id();
        }

        $plannedEvent->fill($data)->save();

        Alert::info('Плановое мероприятие сохранено.');

        return redirect()->route('platform.planned-events');
    }

    public function remove(PlannedEvent $plannedEvent)
    {
        $plannedEvent->delete();

        Alert::info('Плановое мероприятие удалено.');

        return redirect()->route('platform.planned-events');
    }
}