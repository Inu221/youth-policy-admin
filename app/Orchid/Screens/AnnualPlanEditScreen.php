<?php

namespace App\Orchid\Screens;

use App\Models\AnnualPlan;
use App\Models\Department;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class AnnualPlanEditScreen extends Screen
{
    public ?AnnualPlan $annualPlan = null;

    public function query(AnnualPlan $annualPlan): iterable
    {
        $user = auth()->user();

        // Check access for existing plan
        if ($annualPlan->exists) {
            abort_unless($user->can('view', $annualPlan), 403);
        }

        return [
            'annualPlan' => $annualPlan,
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
        return 'Карточка годового плана';
    }

    public function commandBar(): iterable
    {
        $user = auth()->user();
        $buttons = [];

        // Save button (can't edit approved/closed plans unless director)
        $canEdit = !$this->annualPlan?->exists
            || $this->annualPlan->status === AnnualPlan::STATUS_DRAFT
            || $user->isDirector();

        $buttons[] = Button::make('Сохранить')
            ->icon('bs.check-circle')
            ->method('save')
            ->canSee($canEdit && $user->can('update', $this->annualPlan ?? AnnualPlan::class));

        // Approve button (only director, only for draft plans)
        if ($this->annualPlan?->exists && $user->isDirector() && $this->annualPlan->status === AnnualPlan::STATUS_DRAFT) {
            $buttons[] = Button::make('Утвердить план')
                ->icon('bs.check-circle-fill')
                ->method('approve')
                ->type(\Orchid\Support\Color::SUCCESS())
                ->confirm('Утвердить план работы?');
        }

        // Close button (only director, only for approved plans)
        if ($this->annualPlan?->exists && $user->isDirector() && $this->annualPlan->status === AnnualPlan::STATUS_APPROVED) {
            $buttons[] = Button::make('Закрыть план')
                ->icon('bs.lock-fill')
                ->method('closePlan')
                ->type(\Orchid\Support\Color::WARNING())
                ->confirm('Закрыть план работы? После закрытия план нельзя будет редактировать.');
        }

        // Delete button
        $buttons[] = Button::make('Удалить')
            ->icon('bs.trash3')
            ->method('remove')
            ->canSee($this->annualPlan?->exists && $user->can('delete', $this->annualPlan));

        return $buttons;
    }

    public function layout(): iterable
    {
        $user = auth()->user();

        return [
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
    }

    public function save(AnnualPlan $annualPlan, Request $request)
    {
        $user = auth()->user();

        // Check permissions
        if ($annualPlan->exists) {
            // Can't edit approved/closed plans unless director
            if (($annualPlan->status === AnnualPlan::STATUS_APPROVED || $annualPlan->status === AnnualPlan::STATUS_CLOSED) && !$user->isDirector()) {
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

        // Force department_id for department_head
        if ($user->isDepartmentHead()) {
            $data['department_id'] = $user->department_id;
        }

        // Set default status for new plans
        if (! $annualPlan->exists) {
            $data['status'] = AnnualPlan::STATUS_DRAFT;
            $data['created_by'] = auth()->id();
        }

        $annualPlan->fill($data)->save();

        Alert::info('Годовой план сохранен.');

        return redirect()->route('platform.annual-plans');
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