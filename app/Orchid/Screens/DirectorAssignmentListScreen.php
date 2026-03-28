<?php

namespace App\Orchid\Screens;

use App\Models\DirectorAssignment;
use App\Orchid\Layouts\DirectorAssignmentListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class DirectorAssignmentListScreen extends Screen
{
    public function query(): iterable
    {
        $user = auth()->user();

        return [
            'assignments' => DirectorAssignment::with(['department', 'creator'])
                ->forUser($user)
                ->orderByDesc('created_at')
                ->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Поручения от руководителя';
    }

    public function description(): ?string
    {
        return 'Список поручений для подразделений';
    }

    public function commandBar(): iterable
    {
        $user = auth()->user();

        return [
            Link::make('Создать поручение')
                ->icon('bs.plus-circle')
                ->route('platform.director-assignments.create')
                ->canSee($user->can('create', DirectorAssignment::class)),
        ];
    }

    public function layout(): iterable
    {
        return [
            DirectorAssignmentListLayout::class,
        ];
    }
}
