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
        return [
            Button::make('Сохранить')
                ->icon('bs.check-circle')
                ->method('save'),

            Button::make('Удалить')
                ->icon('bs.trash3')
                ->method('remove')
                ->canSee($this->annualPlan?->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Relation::make('annualPlan.department_id')
                    ->title('Подразделение')
                    ->fromModel(Department::class, 'name')
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
                    ->required(),

                Input::make('annualPlan.approval_comment')
                    ->title('Комментарий')
                    ->help('Опционально'),
            ]),
        ];
    }

    public function save(AnnualPlan $annualPlan, Request $request)
    {
        $validated = $request->validate([
            'annualPlan.department_id' => ['required', 'integer', 'exists:departments,id'],
            'annualPlan.year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'annualPlan.title' => ['required', 'string', 'max:255'],
            'annualPlan.status' => ['required', 'in:draft,approved,closed'],
            'annualPlan.approval_comment' => ['nullable', 'string'],
        ]);

        $data = $validated['annualPlan'];

        if (! $annualPlan->exists) {
            $data['created_by'] = auth()->id();
        }

        if ($data['status'] === AnnualPlan::STATUS_APPROVED) {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
        } elseif ($data['status'] === AnnualPlan::STATUS_DRAFT) {
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        }

        $annualPlan->fill($data)->save();

        Alert::info('Годовой план сохранен.');

        return redirect()->route('platform.annual-plans');
    }

    public function remove(AnnualPlan $annualPlan)
    {
        $annualPlan->delete();

        Alert::info('Годовой план удален.');

        return redirect()->route('platform.annual-plans');
    }
}