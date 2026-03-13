<?php

namespace App\Orchid\Layouts;

use App\Models\Department;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class DepartmentListLayout extends Table
{
    protected $target = 'departments';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')
                ->sort(),

            TD::make('name', 'Название')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(function (Department $department) {
                    return Link::make($department->name)
                        ->route('platform.departments.edit', $department);
                }),

            TD::make('short_name', 'Краткое название'),

            TD::make('responsible_user', 'Ответственный')
                ->render(function (Department $department) {
                    return $department->responsibleUser?->full_name ?? '—';
                }),

            TD::make('status', 'Статус')
                ->sort()
                ->render(function (Department $department) {
                    return match ($department->status) {
                        Department::STATUS_ACTIVE => 'Активно',
                        Department::STATUS_ARCHIVED => 'Архив',
                        default => $department->status,
                    };
                }),

            TD::make('updated_at', 'Обновлено')
                ->sort()
                ->render(function (Department $department) {
                    return $department->updated_at?->format('d.m.Y H:i') ?? '—';
                }),
        ];
    }
}