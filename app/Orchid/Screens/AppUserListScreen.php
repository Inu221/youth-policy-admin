<?php

namespace App\Orchid\Screens;

use App\Models\User;
use App\Orchid\Layouts\AppUserListLayout;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class AppUserListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'users' => User::with('department')
                ->filters()
                ->defaultSort('id', 'desc')
                ->paginate(15),
        ];
    }

    public function name(): ?string
    {
        return 'Пользователи';
    }

    public function description(): ?string
    {
        return 'Сотрудники системы';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Создать пользователя')
                ->icon('bs.person-plus')
                ->route('platform.app-users.create'),
        ];
    }

    public function layout(): iterable
    {
        return [
            AppUserListLayout::class,
        ];
    }
}