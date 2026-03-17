<?php

namespace App\Orchid\Screens;

use App\Models\ActualEvent;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;

class CalendarScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Календарь мероприятий';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Планирование и просмотр мероприятий в календарном виде';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        $user = auth()->user();

        return [
            Link::make('Создать мероприятие')
                ->icon('bs.plus-circle')
                ->route('platform.actual-events.create')
                ->canSee($user->isDirector() || $user->isDepartmentHead()),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('platform.calendar'),
        ];
    }
}
