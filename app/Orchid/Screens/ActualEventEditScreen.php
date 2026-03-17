<?php

namespace App\Orchid\Screens;

use App\Models\ActualEvent;
use App\Models\Department;
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

class ActualEventEditScreen extends Screen
{
    public ?ActualEvent $actualEvent = null;

    public function query(ActualEvent $actualEvent): iterable
    {
        $user = auth()->user();

        // Check access for existing event
        if ($actualEvent->exists) {
            abort_unless($user->can('view', $actualEvent), 403);
        }

        return [
            'actualEvent' => $actualEvent,
        ];
    }

    public function name(): ?string
    {
        return $this->actualEvent?->exists
            ? 'Редактирование фактического мероприятия'
            : 'Создание фактического мероприятия';
    }

    public function description(): ?string
    {
        return 'Карточка фактического мероприятия';
    }

    public function commandBar(): iterable
    {
        $user = auth()->user();

        return [
            Button::make('Сохранить')
                ->icon('bs.check-circle')
                ->method('save')
                ->canSee(
                    ($this->actualEvent?->exists && $user->can('update', $this->actualEvent))
                    || (!$this->actualEvent?->exists && $user->can('create', ActualEvent::class))
                ),

            Button::make('Удалить')
                ->icon('bs.trash3')
                ->method('remove')
                ->canSee($this->actualEvent?->exists && $user->can('delete', $this->actualEvent)),
        ];
    }

    public function layout(): iterable
    {
        $user = auth()->user();

        return [
            Layout::rows([
                Relation::make('actualEvent.department_id')
                    ->title('Подразделение')
                    ->fromModel(Department::class, 'name')
                    ->applyScope('forUser', $user)
                    ->disabled($user->isDepartmentHead())
                    ->value($user->isDepartmentHead() ? $user->department_id : null)
                    ->required(),

                Relation::make('actualEvent.planned_event_id')
                    ->title('Плановое мероприятие')
                    ->fromModel(PlannedEvent::class, 'title')
                    ->applyScope('forUser', $user)
                    ->help('Можно оставить пустым для внепланового мероприятия'),

                Input::make('actualEvent.title')
                    ->title('Название мероприятия')
                    ->required(),

                Input::make('actualEvent.description')
                    ->title('Описание'),

                DateTimer::make('actualEvent.actual_start_at')
                    ->title('Дата и время начала')
                    ->enableTime()
                    ->required(),

                DateTimer::make('actualEvent.actual_end_at')
                    ->title('Дата и время окончания')
                    ->enableTime(),

                Input::make('actualEvent.location_name')
                    ->title('Место проведения'),

                Input::make('actualEvent.location_url')
                    ->title('Ссылка на место / гео-ссылка'),

                Relation::make('actualEvent.responsible_user_id')
                    ->title('Ответственный')
                    ->fromModel(User::class, 'full_name')
                    ->required(),

                Input::make('actualEvent.planned_participants_snapshot')
                    ->title('Плановое количество участников (snapshot)')
                    ->type('number'),

                Input::make('actualEvent.actual_participants_count')
                    ->title('Фактическое количество участников')
                    ->type('number')
                    ->required(),

                Select::make('actualEvent.status')
                    ->title('Статус')
                    ->options([
                        ActualEvent::STATUS_PLANNED => 'Запланировано',
                        ActualEvent::STATUS_IN_PROGRESS => 'Проводится',
                        ActualEvent::STATUS_ARCHIVED => 'Архив',
                        ActualEvent::STATUS_CANCELLED => 'Отменено',
                    ])
                    ->required(),
            ]),
        ];
    }

    public function save(ActualEvent $actualEvent, Request $request)
    {
        $user = auth()->user();

        // Check permissions
        if ($actualEvent->exists) {
            abort_unless($user->can('update', $actualEvent), 403);
        } else {
            abort_unless($user->can('create', ActualEvent::class), 403);
        }

        $validated = $request->validate([
            'actualEvent.department_id' => ['required', 'integer', 'exists:departments,id'],
            'actualEvent.planned_event_id' => ['nullable', 'integer', 'exists:planned_events,id'],
            'actualEvent.title' => ['required', 'string', 'max:255'],
            'actualEvent.description' => ['nullable', 'string'],
            'actualEvent.actual_start_at' => ['required', 'date'],
            'actualEvent.actual_end_at' => ['nullable', 'date', 'after_or_equal:actualEvent.actual_start_at'],
            'actualEvent.location_name' => ['nullable', 'string', 'max:255'],
            'actualEvent.location_url' => ['nullable', 'string', 'max:1000'],
            'actualEvent.responsible_user_id' => ['required', 'integer', 'exists:users,id'],
            'actualEvent.planned_participants_snapshot' => ['nullable', 'integer', 'min:0'],
            'actualEvent.actual_participants_count' => ['required', 'integer', 'min:0'],
            'actualEvent.status' => ['required', 'in:planned,in_progress,archived,cancelled'],
        ]);

        $data = $validated['actualEvent'];

        // Force department_id for department_head
        if ($user->isDepartmentHead()) {
            $data['department_id'] = $user->department_id;
        }

        if (! $actualEvent->exists) {
            $data['created_by'] = auth()->id();
        } else {
            $data['updated_by'] = auth()->id();
        }

        if ($data['status'] === ActualEvent::STATUS_ARCHIVED) {
            $data['completed_at'] = now();
        } elseif (($data['status'] ?? null) !== ActualEvent::STATUS_ARCHIVED) {
            $data['completed_at'] = null;
        }

        if (!empty($data['planned_event_id']) && empty($data['planned_participants_snapshot'])) {
            $plannedEvent = PlannedEvent::find($data['planned_event_id']);
            if ($plannedEvent) {
                $data['planned_participants_snapshot'] = $plannedEvent->planned_participants_count;
            }
        }

        $actualEvent->fill($data)->save();

        Alert::info('Фактическое мероприятие сохранено.');

        return redirect()->route('platform.actual-events');
    }

    public function remove(ActualEvent $actualEvent)
    {
        abort_unless(auth()->user()->can('delete', $actualEvent), 403);

        $actualEvent->delete();

        Alert::info('Фактическое мероприятие удалено.');

        return redirect()->route('platform.actual-events');
    }
}