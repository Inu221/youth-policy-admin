<?php

namespace App\Orchid\Layouts;

use App\Models\DirectorAssignment;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class DirectorAssignmentListLayout extends Table
{
    protected $target = 'assignments';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')
                ->width('70')
                ->sort(),

            TD::make('title', 'Название')
                ->sort()
                ->render(function (DirectorAssignment $assignment) {
                    return Link::make($assignment->title)
                        ->route('platform.director-assignments.edit', $assignment);
                }),

            TD::make('department', 'Подразделение')
                ->render(function (DirectorAssignment $assignment) {
                    return $assignment->department?->display_name ?? '—';
                }),

            TD::make('status', 'Статус')
                ->sort()
                ->render(function (DirectorAssignment $assignment) {
                    $badges = [
                        DirectorAssignment::STATUS_PENDING => 'secondary',
                        DirectorAssignment::STATUS_IN_PROGRESS => 'primary',
                        DirectorAssignment::STATUS_COMPLETED => 'success',
                    ];

                    $badge = $badges[$assignment->status] ?? 'secondary';

                    return "<span class='badge bg-{$badge}'>{$assignment->status_label}</span>";
                }),

            TD::make('due_date', 'Срок')
                ->sort()
                ->render(function (DirectorAssignment $assignment) {
                    if (!$assignment->due_date) {
                        return '—';
                    }

                    $formatted = $assignment->due_date->format('d.m.Y');

                    if ($assignment->is_overdue) {
                        return "<span class='text-danger'>⚠️ {$formatted}</span>";
                    }

                    return $formatted;
                }),

            TD::make('creator', 'Создал')
                ->render(function (DirectorAssignment $assignment) {
                    return $assignment->creator?->full_name ?? '—';
                }),

            TD::make('created_at', 'Создано')
                ->sort()
                ->render(function (DirectorAssignment $assignment) {
                    return $assignment->created_at?->format('d.m.Y H:i') ?? '—';
                }),
        ];
    }
}
