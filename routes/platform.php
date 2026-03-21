<?php

declare(strict_types=1);

use App\Orchid\Screens\Examples\ExampleActionsScreen;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleGridScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;
use App\Orchid\Screens\DepartmentEditScreen;
use App\Orchid\Screens\DepartmentListScreen;
use App\Orchid\Screens\AppUserEditScreen;
use App\Orchid\Screens\AppUserListScreen;
use App\Orchid\Screens\AnnualPlanEditScreen;
use App\Orchid\Screens\AnnualPlanListScreen;
use App\Orchid\Screens\PlannedEventEditScreen;
use App\Orchid\Screens\PlannedEventListScreen;
use App\Orchid\Screens\ActualEventEditScreen;
use App\Orchid\Screens\ActualEventListScreen;
use App\Orchid\Screens\ActualEventLinkEditScreen;
use App\Orchid\Screens\ActualEventLinkListScreen;
use App\Orchid\Screens\ParticipantEditScreen;
use App\Orchid\Screens\ParticipantListScreen;
use App\Orchid\Screens\ActualEventParticipantEditScreen;
use App\Orchid\Screens\ActualEventParticipantListScreen;
use App\Orchid\Screens\ActualEventFileListScreen;
use App\Orchid\Screens\ActualEventFileEditScreen;
use App\Orchid\Screens\AuditLogListScreen;
use App\Orchid\Screens\CalendarScreen;
use App\Orchid\Screens\ReportsScreen;
use App\Http\Controllers\CalendarApiController;


/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

Route::screen('calendar', CalendarScreen::class)
    ->name('platform.calendar');

Route::get('api/calendar/events', [CalendarApiController::class, 'events'])
    ->name('platform.calendar.events');

Route::screen('audit-logs', AuditLogListScreen::class)
    ->name('platform.audit-logs');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// Example...
Route::screen('example', ExampleScreen::class)
    ->name('platform.example')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push('Example Screen'));

Route::screen('departments', DepartmentListScreen::class)
    ->name('platform.departments');

Route::screen('departments/create', DepartmentEditScreen::class)
    ->name('platform.departments.create');

Route::screen('departments/{department}/edit', DepartmentEditScreen::class)
    ->name('platform.departments.edit');

Route::screen('app-users', AppUserListScreen::class)
    ->name('platform.app-users');

Route::screen('app-users/create', AppUserEditScreen::class)
    ->name('platform.app-users.create');

Route::screen('app-users/{user}/edit', AppUserEditScreen::class)
    ->name('platform.app-users.edit');

Route::screen('annual-plans', AnnualPlanListScreen::class)
    ->name('platform.annual-plans');

Route::screen('annual-plans/create', AnnualPlanEditScreen::class)
    ->name('platform.annual-plans.create');

Route::screen('annual-plans/{annualPlan}/edit', AnnualPlanEditScreen::class)
    ->name('platform.annual-plans.edit');

Route::screen('planned-events', PlannedEventListScreen::class)
    ->name('platform.planned-events');

Route::screen('planned-events/create', PlannedEventEditScreen::class)
    ->name('platform.planned-events.create');

Route::screen('planned-events/{plannedEvent}/edit', PlannedEventEditScreen::class)
    ->name('platform.planned-events.edit');

Route::screen('actual-events', ActualEventListScreen::class)
    ->name('platform.actual-events');

Route::screen('actual-events/create', ActualEventEditScreen::class)
    ->name('platform.actual-events.create');

Route::screen('actual-events/{actualEvent}/edit', ActualEventEditScreen::class)
    ->name('platform.actual-events.edit');

Route::screen('actual-event-links', ActualEventLinkListScreen::class)
    ->name('platform.actual-event-links');

Route::screen('actual-event-links/create', ActualEventLinkEditScreen::class)
    ->name('platform.actual-event-links.create');

Route::screen('actual-event-links/{actualEventLink}/edit', ActualEventLinkEditScreen::class)
    ->name('platform.actual-event-links.edit');

Route::screen('participants', ParticipantListScreen::class)
    ->name('platform.participants');

Route::screen('participants/create', ParticipantEditScreen::class)
    ->name('platform.participants.create');

Route::screen('participants/{participant}/edit', ParticipantEditScreen::class)
    ->name('platform.participants.edit');

Route::screen('actual-event-participants', ActualEventParticipantListScreen::class)
    ->name('platform.actual-event-participants');

Route::screen('actual-event-participants/create', ActualEventParticipantEditScreen::class)
    ->name('platform.actual-event-participants.create');

Route::screen('actual-event-participants/{actualEventParticipant}/edit', ActualEventParticipantEditScreen::class)
    ->name('platform.actual-event-participants.edit');

Route::screen('actual-event-files', ActualEventFileListScreen::class)
    ->name('platform.actual-event-files');

Route::screen('actual-event-files/create', ActualEventFileEditScreen::class)
    ->name('platform.actual-event-files.create');

Route::screen('actual-event-files/{file}/edit', ActualEventFileEditScreen::class)
    ->name('platform.actual-event-files.edit');

Route::get('actual-event-files/{file}/download', [\App\Http\Controllers\ActualEventFileController::class, 'download'])
    ->name('platform.actual-event-files.download');

Route::screen('reports', ReportsScreen::class)
    ->name('platform.reports');

Route::screen('/examples/form/fields', ExampleFieldsScreen::class)->name('platform.example.fields');
Route::screen('/examples/form/advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');
Route::screen('/examples/form/editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
Route::screen('/examples/form/actions', ExampleActionsScreen::class)->name('platform.example.actions');

Route::screen('/examples/layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
Route::screen('/examples/grid', ExampleGridScreen::class)->name('platform.example.grid');
Route::screen('/examples/charts', ExampleChartsScreen::class)->name('platform.example.charts');
Route::screen('/examples/cards', ExampleCardsScreen::class)->name('platform.example.cards');

// Route::screen('idea', Idea::class, 'platform.screens.idea');
