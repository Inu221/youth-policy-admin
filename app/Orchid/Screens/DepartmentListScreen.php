<?php

namespace App\Orchid\Screens;

use App\Models\Department;
use App\Orchid\Layouts\DepartmentListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class DepartmentListScreen extends Screen
{
    public function query(): iterable
    {
        $user = auth()->user();

        return [
            'departments' => Department::with('responsibleUser')
                ->forUser($user)
                ->orderByDesc('id')
                ->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Подразделения';
    }

    public function description(): ?string
    {
        return 'Список муниципалитетов и управлений';
    }

    public function commandBar(): iterable
    {
        $user = auth()->user();

        return [
            Link::make('Создать подразделение')
                ->icon('bs.plus-circle')
                ->route('platform.departments.create')
                ->canSee($user->can('create', Department::class)),
        ];
    }

    public function layout(): iterable
    {
        return [
            DepartmentListLayout::class,
        ];
    }
}