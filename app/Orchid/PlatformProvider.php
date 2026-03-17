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
        return [
            // Menu::make('Get Started')
            //     ->icon('bs.book')
            //     ->title('Navigation')
            //     ->route(config('platform.index')),

            // Menu::make('Sample Screen')
            //     ->icon('bs.collection')
            //     ->route('platform.example')
            //     ->badge(fn () => 6),

            // Menu::make('Form Elements')
            //     ->icon('bs.card-list')
            //     ->route('platform.example.fields')
            //     ->active('*/examples/form/*'),

            // Menu::make('Layouts Overview')
            //     ->icon('bs.window-sidebar')
            //     ->route('platform.example.layouts'),

            // Menu::make('Grid System')
            //     ->icon('bs.columns-gap')
            //     ->route('platform.example.grid'),

            // Menu::make('Charts')
            //     ->icon('bs.bar-chart')
            //     ->route('platform.example.charts'),

            // Menu::make('Cards')
            //     ->icon('bs.card-text')
            //     ->route('platform.example.cards')
            //     ->divider(),

            // Menu::make(__('Users'))
            //     ->icon('bs.people')
            //     ->route('platform.systems.users')
            //     ->permission('platform.systems.users')
            //     ->title(__('Access Controls')),

            // Menu::make(__('Roles'))
            //     ->icon('bs.shield')
            //     ->route('platform.systems.roles')
            //     ->permission('platform.systems.roles')
            //     ->divider(),

            // Menu::make('Documentation')
            //     ->title('Docs')
            //     ->icon('bs.box-arrow-up-right')
            //     ->url('https://orchid.software/en/docs')
            //     ->target('_blank'),

            // Menu::make('Changelog')
            //     ->icon('bs.box-arrow-up-right')
            //     ->url('https://github.com/orchidsoftware/platform/blob/master/CHANGELOG.md')
            //     ->target('_blank')
            //     ->badge(fn () => Dashboard::version(), Color::DARK),

            Menu::make('Подразделения')
                ->icon('bs.buildings')
                ->route('platform.departments'),

            Menu::make('Пользователи')
                ->icon('bs.people')
                ->route('platform.app-users'),
            
            Menu::make('Годовые планы')
                ->icon('bs.calendar3')
                ->route('platform.annual-plans'),
        
            Menu::make('Плановые мероприятия')
                ->icon('bs.list-task')
                ->route('platform.planned-events'),

            Menu::make('Фактические мероприятия')
                ->icon('bs.clipboard-check')
                ->route('platform.actual-events'),

            Menu::make('Ссылки мероприятий')
                ->icon('bs.link-45deg')
                ->route('platform.actual-event-links'),

            Menu::make('Участники')
                ->icon('bs.people-fill')
                ->route('platform.participants'),

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
