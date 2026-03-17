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
        return [
            'annualPlans' => AnnualPlan::with(['department', 'creator', 'approver'])
                ->orderByDesc('year')
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
        return 'Планы работы по управлениям';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Создать план')
                ->icon('bs.plus-circle')
                ->route('platform.annual-plans.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            AnnualPlanListLayout::class,
        ];
    }
}