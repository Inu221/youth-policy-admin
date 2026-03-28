<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        $user = auth()->user();

        return [
            Menu::make('Панель управления')
                ->icon('bs.speedometer2')
                ->route('platform.main')
                ->title('Главная'),

            Menu::make('Отчеты')
                ->icon('bs.bar-chart-line')
                ->route('platform.reports'),

            Menu::make('Календарь')
                ->icon('bs.calendar3')
                ->route('platform.calendar')
                ->divider(),

            Menu::make('Журнал действий')
                ->icon('bs.journal-text')
                ->route('platform.audit-logs')
                ->canSee($user && $user->isDirector())
                ->title('Аудит'),

            Menu::make('Рейтинг муниципалитетов')
                ->icon('bs.trophy')
                ->route('platform.department-ranking')
                ->canSee($user && ($user->isDirector() || $user->isAnalyst())),

            Menu::make('Поручения руководителя')
                ->icon('bs.clipboard-check')
                ->route('platform.director-assignments')
                ->canSee($user && ($user->isDirector() || $user->isDepartmentHead()))
                ->title('Поручения'),

            Menu::make('Подразделения')
                ->icon('bs.buildings')
                ->route('platform.departments')
                ->canSee($user && ($user->isDirector() || $user->isDepartmentHead()))
                ->title('Управление'),

            Menu::make('Пользователи')
                ->icon('bs.people')
                ->route('platform.app-users')
                ->canSee($user && $user->isDirector()),
            
            Menu::make('Годовые планы')
                ->icon('bs.calendar3')
                ->route('platform.annual-plans')
                ->title('Планирование')
                ->divider(),
        
            Menu::make('Плановые мероприятия')
                ->icon('bs.list-task')
                ->route('platform.planned-events'),

            Menu::make('Фактические мероприятия')
                ->icon('bs.clipboard-check')
                ->route('platform.actual-events')
                ->title('Исполнение')
                ->divider(),

            Menu::make('Файлы мероприятий')
                ->icon('bs.file-earmark-arrow-up')
                ->route('platform.actual-event-files'),

            Menu::make('Ссылки мероприятий')
                ->icon('bs.link-45deg')
                ->route('platform.actual-event-links'),

            Menu::make('Участники')
                ->icon('bs.people-fill')
                ->route('platform.participants')
                ->title('Участники')
                ->divider(),

            Menu::make('Участники мероприятий')
                ->icon('bs.person-lines-fill')
                ->route('platform.actual-event-participants'),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
