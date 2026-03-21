<?php

namespace App\Orchid\Layouts;

use App\Models\AnnualPlan;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class AnnualPlanListLayout extends Table
{
    protected $target = 'annualPlans';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),

            TD::make('title', 'Название')
                ->render(function (AnnualPlan $annualPlan) {
                    return Link::make($annualPlan->title)
                        ->route('platform.annual-plans.edit', $annualPlan);
                }),

            TD::make('department', 'Подразделение')
                ->render(function (AnnualPlan $annualPlan) {
                    return $annualPlan->department?->display_name ?? '—';
                }),

            TD::make('year', 'Год'),

            TD::make('planned_events_count', 'Плановых мероприятий')
                ->align(TD::ALIGN_CENTER)
                ->render(function (AnnualPlan $annualPlan) {
                    return (string) ($annualPlan->planned_events_count ?? 0);
                }),

            TD::make('status', 'Статус')
                ->render(function (AnnualPlan $annualPlan) {
                    return match ($annualPlan->status) {
                        AnnualPlan::STATUS_DRAFT => 'Черновик',
                        AnnualPlan::STATUS_APPROVED => 'Утвержден',
                        AnnualPlan::STATUS_CLOSED => 'Закрыт',
                        default => $annualPlan->status,
                    };
                }),

            TD::make('creator', 'Создал')
                ->render(function (AnnualPlan $annualPlan) {
                    return $annualPlan->creator?->full_name ?? '—';
                }),

            TD::make('approver', 'Утвердил')
                ->render(function (AnnualPlan $annualPlan) {
                    return $annualPlan->approver?->full_name ?? '—';
                }),

            TD::make('updated_at', 'Обновлено')
                ->render(function (AnnualPlan $annualPlan) {
                    return $annualPlan->updated_at?->format('d.m.Y H:i') ?? '—';
                }),

            TD::make('actions', 'Действия')
                ->align(TD::ALIGN_CENTER)
                ->render(function (AnnualPlan $annualPlan) {
                    return Link::make('Открыть план')
                        ->icon('bs.box-arrow-up-right')
                        ->route('platform.annual-plans.edit', $annualPlan);
                }),
        ];
    }
}
