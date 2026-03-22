<?php

namespace App\Orchid\Screens;

use App\Models\AnnualPlan;
use App\Models\Department;
use App\Models\PlannedEvent;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class AnnualPlanEditScreen extends Screen
{
    public ?AnnualPlan $annualPlan = null;

    public function query(AnnualPlan $annualPlan): iterable
    {
        $user = auth()->user();

        if ($annualPlan->exists) {
            abort_unless($user->can('view', $annualPlan), 403);
        }

        $plannedEvents = [];
        if ($annualPlan->exists) {
            $plannedEvents = PlannedEvent::where('annual_plan_id', $annualPlan->id)
                ->with(['responsibleUser'])
                ->orderBy('planned_start_at')
                ->get();
        }

        return [
            'annualPlan' => $annualPlan,
            'plannedEvents' => $plannedEvents,
        ];
    }

    public function name(): ?string
    {
        return $this->annualPlan?->exists
            ? 'Редактирование годового плана'
            : 'Создание годового плана';
    }

    public function description(): ?string
    {
        return $this->annualPlan?->exists
            ? 'Внутри плана можно добавлять и редактировать плановые мероприятия.'
            : 'Сначала сохраните план, после этого появится кнопка добавления мероприятий.';
    }

    public function commandBar(): iterable
    {
        $user = auth()->user();
        $buttons = [];

        $canManagePlannedEvents = $this->annualPlan?->exists
            && $user->can('view', $this->annualPlan)
            && $user->can('create', PlannedEvent::class)
            && ($user->isDirector() || $this->annualPlan->status === AnnualPlan::STATUS_DRAFT);

        if ($canManagePlannedEvents) {
            $buttons[] = Link::make('Добавить мероприятие')
                ->icon('bs.plus-circle')
                ->route('platform.planned-events.create', [
                    'annual_plan_id' => $this->annualPlan->id,
                    'from_annual_plan' => 1,
                ])
                ->type(\Orchid\Support\Color::PRIMARY());
        }

        $canSave = ($this->annualPlan?->exists && $user->can('update', $this->annualPlan))
            || (! $this->annualPlan?->exists && $user->can('create', AnnualPlan::class));

        $buttons[] = Button::make('Сохранить')
            ->icon('bs.check-circle')
            ->method('save')
            ->canSee($canSave);

        if ($this->annualPlan?->exists && $user->isDirector() && $this->annualPlan->status === AnnualPlan::STATUS_DRAFT) {
            $buttons[] = Button::make('Утвердить план')
                ->icon('bs.check-circle-fill')
                ->method('approve')
                ->type(\Orchid\Support\Color::SUCCESS())
                ->confirm('Утвердить план работы?');
        }

        if ($this->annualPlan?->exists && $user->isDirector() && $this->annualPlan->status === AnnualPlan::STATUS_APPROVED) {
            $buttons[] = Button::make('Закрыть план')
                ->icon('bs.lock-fill')
                ->method('closePlan')
                ->type(\Orchid\Support\Color::WARNING())
                ->confirm('Закрыть план работы? После закрытия план нельзя будет редактировать.');
        }

        $buttons[] = Button::make('Удалить')
            ->icon('bs.trash3')
            ->method('remove')
            ->canSee($this->annualPlan?->exists && $user->can('delete', $this->annualPlan));

        return $buttons;
    }

    public function layout(): iterable
    {
        $user = auth()->user();

        $layouts = [
            Layout::rows([
                Relation::make('annualPlan.department_id')
                    ->title('Подразделение')
                    ->fromModel(Department::class, 'name')
                    ->applyScope('forUser', $user)
                    ->disabled($user->isDepartmentHead())
                    ->value($user->isDepartmentHead() ? $user->department_id : null)
                    ->required(),

                Input::make('annualPlan.year')
                    ->title('Год')
                    ->type('number')
                    ->required(),

                Input::make('annualPlan.title')
                    ->title('Название плана')
                    ->required(),

                Select::make('annualPlan.status')
                    ->title('Статус')
                    ->options([
                        AnnualPlan::STATUS_DRAFT => 'Черновик',
                        AnnualPlan::STATUS_APPROVED => 'Утвержден',
                        AnnualPlan::STATUS_CLOSED => 'Закрыт',
                    ])
                    ->disabled(true)
                    ->help('Статус изменяется через кнопки "Утвердить" и "Закрыть"')
                    ->required(),

                Input::make('annualPlan.approval_comment')
                    ->title('Комментарий')
                    ->help('Опционально'),
            ]),
        ];

        if ($this->annualPlan?->exists) {
            $layouts[] = new class extends \Orchid\Screen\Layouts\Table
            {
                protected $target = 'plannedEvents';

                protected $title = 'Плановые мероприятия';

                protected function columns(): iterable
                {
                    return [
                        TD::make('title', 'Название мероприятия')
                            ->render(fn (PlannedEvent $event) => Link::make($event->title)
                                ->route('platform.planned-events.edit', [
                                    'plannedEvent' => $event->id,
                                    'from_annual_plan' => 1,
                                ])),

                        TD::make('planned_start_at', 'Дата начала')
                            ->render(fn (PlannedEvent $event) => $event->planned_start_at?->format('d.m.Y H:i')),

                        TD::make('responsible_user_id', 'Ответственный')
                            ->render(fn (PlannedEvent $event) => $event->responsibleUser?->full_name ?? '—'),

                        TD::make('status', 'Статус')
                            ->render(function (PlannedEvent $event) {
                                $statusLabels = [
                                    PlannedEvent::STATUS_PLANNED => 'Запланировано',
                                    PlannedEvent::STATUS_IN_PROGRESS => 'Проводится',
                                    PlannedEvent::STATUS_ARCHIVED => 'Архив',
                                    PlannedEvent::STATUS_CANCELLED => 'Отменено',
                                ];

                                return $statusLabels[$event->status] ?? $event->status;
                            }),
                    ];
                }

                protected function textNotFound(): string
                {
                    return 'Нет плановых мероприятий';
                }

                protected function subNotFound(): string
                {
                    return 'Нажмите "Добавить мероприятие" для создания нового.';
                }
            };
        }

        return $layouts;
    }

    public function save(AnnualPlan $annualPlan, Request $request)
    {
        $user = auth()->user();

        if ($annualPlan->exists) {
            if (($annualPlan->status === AnnualPlan::STATUS_APPROVED || $annualPlan->status === AnnualPlan::STATUS_CLOSED) && ! $user->isDirector()) {
                abort(403, 'Нельзя редактировать утвержденный или закрытый план.');
            }
            abort_unless($user->can('update', $annualPlan), 403);
        } else {
            abort_unless($user->can('create', AnnualPlan::class), 403);
        }

        $validated = $request->validate([
            'annualPlan.department_id' => ['required', 'integer', 'exists:departments,id'],
            'annualPlan.year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'annualPlan.title' => ['required', 'string', 'max:255'],
            'annualPlan.approval_comment' => ['nullable', 'string'],
        ]);

        $data = $validated['annualPlan'];

        if ($user->isDepartmentHead()) {
            $data['department_id'] = $user->department_id;
        }

        if (! $annualPlan->exists) {
            $data['status'] = AnnualPlan::STATUS_DRAFT;
            $data['created_by'] = auth()->id();
        }

        $annualPlan->fill($data)->save();

        Alert::info('Годовой план сохранен. Теперь внутри него можно добавлять мероприятия.');

        return redirect()->route('platform.annual-plans.edit', $annualPlan);
    }

    public function approve(AnnualPlan $annualPlan)
    {
        $user = auth()->user();
        abort_unless($user->isDirector(), 403);
        abort_unless($annualPlan->status === AnnualPlan::STATUS_DRAFT, 400, 'Можно утверждать только черновики.');

        $annualPlan->update([
            'status' => AnnualPlan::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        Alert::success('Годовой план утвержден.');

        return redirect()->route('platform.annual-plans');
    }

    public function closePlan(AnnualPlan $annualPlan)
    {
        $user = auth()->user();
        abort_unless($user->isDirector(), 403);
        abort_unless($annualPlan->status === AnnualPlan::STATUS_APPROVED, 400, 'Можно закрывать только утвержденные планы.');

        $annualPlan->update([
            'status' => AnnualPlan::STATUS_CLOSED,
        ]);

        Alert::warning('Годовой план закрыт.');

        return redirect()->route('platform.annual-plans');
    }

    public function remove(AnnualPlan $annualPlan)
    {
        $annualPlan->delete();

        Alert::info('Годовой план удален.');

        return redirect()->route('platform.annual-plans');
    }
}
