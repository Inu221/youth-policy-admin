<?php

namespace App\Orchid\Screens;

use App\Models\AnnualPlan;
use App\Orchid\Layouts\AnnualPlanListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class AnnualPlanListScreen extends Screen
{
    public function query(): iterable
    {
        $user = auth()->user();

        return [
            'annualPlans' => AnnualPlan::with(['department', 'creator', 'approver'])
                ->withCount('plannedEvents')
                ->forUser($user)
                ->filters()
                ->defaultSort('year', 'desc')
                ->orderByDesc('id')
                ->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Годовые планы';
    }

    public function description(): ?string
    {
        return 'Планы работы по управлениям. Откройте нужный план, чтобы добавить мероприятия.';
    }

    public function commandBar(): iterable
    {
        $user = auth()->user();

        return [
            Link::make('Создать план')
                ->icon('bs.plus-circle')
                ->route('platform.annual-plans.create')
                ->canSee($user->can('create', AnnualPlan::class)),
        ];
    }

    public function layout(): iterable
    {
        return [
            AnnualPlanListLayout::class,
        ];
    }
}
