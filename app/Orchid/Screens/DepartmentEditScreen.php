<?php

namespace App\Orchid\Screens;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class DepartmentEditScreen extends Screen
{
    public ?Department $department = null;

    public function query(Department $department): iterable
    {
        return [
            'department' => $department,
        ];
    }

    public function name(): ?string
    {
        return $this->department?->exists
            ? 'Редактирование подразделения'
            : 'Создание подразделения';
    }

    public function description(): ?string
    {
        return 'Карточка подразделения';
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
                ->canSee($this->department?->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('department.name')
                    ->title('Название подразделения')
                    ->required()
                    ->placeholder('Например: Управление молодежной политики г. Тестовск'),

                Input::make('department.short_name')
                    ->title('Краткое название')
                    ->placeholder('Например: Тестовск'),

                Select::make('department.status')
                    ->title('Статус')
                    ->options([
                        Department::STATUS_ACTIVE => 'Активно',
                        Department::STATUS_ARCHIVED => 'Архив',
                    ])
                    ->required(),

                Relation::make('department.responsible_user_id')
                    ->title('Ответственное лицо')
                    ->fromModel(User::class, 'full_name')
                    ->displayAppend('full_name')
                    ->help('Можно оставить пустым и назначить позже'),
            ]),
        ];
    }

    public function save(Department $department, Request $request)
    {
        $validated = $request->validate([
            'department.name' => ['required', 'string', 'max:255'],
            'department.short_name' => ['nullable', 'string', 'max:100'],
            'department.status' => ['required', 'in:active,archived'],
            'department.responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $department->fill($validated['department'])->save();

        Alert::info('Подразделение сохранено.');

        return redirect()->route('platform.departments');
    }

    public function remove(Department $department)
    {
        $department->delete();

        Alert::info('Подразделение удалено.');

        return redirect()->route('platform.departments');
    }
}