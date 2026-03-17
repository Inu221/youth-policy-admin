<?php

namespace App\Orchid\Layouts;

use App\Models\User;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class AppUserListLayout extends Table
{
    protected $target = 'users';

    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),

            TD::make('full_name', 'ФИО')
                ->render(function (User $user) {
                    return Link::make($user->full_name ?? $user->name ?? '—')
                        ->route('platform.app-users.edit', $user);
                }),

            TD::make('username', 'Логин'),
            TD::make('email', 'Email'),

            TD::make('role', 'Роль')
                ->render(function (User $user) {
                    return match ($user->role) {
                        User::ROLE_DIRECTOR => 'Руководитель департамента',
                        User::ROLE_DEPARTMENT_HEAD => 'Начальник управления',
                        User::ROLE_ANALYST => 'Аналитик',
                        default => $user->role,
                    };
                }),

            TD::make('department', 'Подразделение')
                ->render(function (User $user) {
                    return $user->department?->display_name ?? '—';
                }),

            TD::make('is_active', 'Активность')
                ->render(fn (User $user) => $user->is_active ? 'Активен' : 'Отключен'),

            TD::make('updated_at', 'Обновлено')
                ->render(fn (User $user) => $user->updated_at?->format('d.m.Y H:i') ?? '—'),
        ];
    }
}