<?php

namespace App\Orchid\Screens;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class AppUserEditScreen extends Screen
{
    public ?User $user = null;

    public function query(User $user): iterable
    {
        return [
            'user' => $user,
        ];
    }

    public function name(): ?string
    {
        return $this->user?->exists
            ? 'Редактирование пользователя'
            : 'Создание пользователя';
    }

    public function description(): ?string
    {
        return 'Карточка пользователя';
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
                ->canSee($this->user?->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('user.full_name')
                    ->title('ФИО')
                    ->required(),

                Input::make('user.username')
                    ->title('Логин')
                    ->required(),

                Input::make('user.email')
                    ->title('Email')
                    ->type('email')
                    ->required(),

                Password::make('user.password')
                    ->title('Пароль')
                    ->help('При редактировании можно оставить пустым'),

                Select::make('user.role')
                    ->title('Роль')
                    ->options([
                        User::ROLE_DIRECTOR => 'Руководитель департамента',
                        User::ROLE_DEPARTMENT_HEAD => 'Начальник управления',
                        User::ROLE_ANALYST => 'Аналитик',
                    ])
                    ->required(),

                Relation::make('user.department_id')
                    ->title('Подразделение')
                    ->fromModel(Department::class, 'name')
                    ->help('Для руководителя департамента можно оставить пустым'),

                Input::make('user.phone')
                    ->title('Телефон'),

                Select::make('user.is_active')
                    ->title('Активность')
                    ->options([
                        1 => 'Активен',
                        0 => 'Отключен',
                    ])
                    ->required(),
            ]),
        ];
    }

    public function save(User $user, Request $request)
    {
        $rules = [
            'user.full_name' => ['required', 'string', 'max:255'],
            'user.username' => ['required', 'string', 'max:100', 'unique:users,username,' . $user->id],
            'user.email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'user.role' => ['required', 'in:director,department_head,analyst'],
            'user.department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'user.phone' => ['nullable', 'string', 'max:50'],
            'user.is_active' => ['required', 'boolean'],
        ];

        $rules['user.password'] = $user->exists
            ? ['nullable', 'string', 'min:6']
            : ['required', 'string', 'min:6'];

        $validated = $request->validate($rules);
        $data = $validated['user'];

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $data['name'] = $data['full_name'];

        if (($data['role'] ?? null) === User::ROLE_DIRECTOR) {
            $data['department_id'] = null;
        }

        if (! $user->exists && empty($user->permissions)) {
            $data['permissions'] = ['platform.index' => true];
        }

        $user->fill($data)->save();

        Alert::info('Пользователь сохранен.');

        return redirect()->route('platform.app-users');
    }

    public function remove(User $user)
    {
        $user->delete();

        Alert::info('Пользователь удален.');

        return redirect()->route('platform.app-users');
    }
}