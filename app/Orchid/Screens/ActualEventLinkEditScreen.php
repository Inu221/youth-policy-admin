<?php

namespace App\Orchid\Screens;

use App\Models\ActualEvent;
use App\Models\ActualEventLink;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ActualEventLinkEditScreen extends Screen
{
    public ?ActualEventLink $actualEventLink = null;

    public function query(ActualEventLink $actualEventLink): iterable
    {
        return [
            'actualEventLink' => $actualEventLink,
        ];
    }

    public function name(): ?string
    {
        return $this->actualEventLink?->exists
            ? 'Редактирование ссылки'
            : 'Создание ссылки';
    }

    public function description(): ?string
    {
        return 'Карточка ссылки фактического мероприятия';
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
                ->canSee($this->actualEventLink?->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Relation::make('actualEventLink.actual_event_id')
                    ->title('Фактическое мероприятие')
                    ->fromModel(ActualEvent::class, 'title')
                    ->required(),

                Select::make('actualEventLink.link_type')
                    ->title('Тип ссылки')
                    ->options([
                        ActualEventLink::TYPE_SOCIAL_POST => 'Пост в соцсети',
                        ActualEventLink::TYPE_MEDIA => 'Медиа',
                        ActualEventLink::TYPE_OTHER => 'Другое',
                    ])
                    ->required(),

                Input::make('actualEventLink.url')
                    ->title('URL')
                    ->type('url')
                    ->required(),

                Select::make('actualEventLink.is_primary')
                    ->title('Основная ссылка')
                    ->options([
                        1 => 'Да',
                        0 => 'Нет',
                    ])
                    ->required(),
            ]),
        ];
    }

    public function save(ActualEventLink $actualEventLink, Request $request)
    {
        $validated = $request->validate([
            'actualEventLink.actual_event_id' => ['required', 'integer', 'exists:actual_events,id'],
            'actualEventLink.link_type' => ['required', 'in:social_post,media,other'],
            'actualEventLink.url' => ['required', 'url', 'max:1000'],
            'actualEventLink.is_primary' => ['required', 'boolean'],
        ]);

        $data = $validated['actualEventLink'];
        $data['created_by'] = $actualEventLink->exists
            ? $actualEventLink->created_by
            : auth()->id();

        if ($actualEventLink->exists && empty($actualEventLink->created_at)) {
            $data['created_at'] = now();
        } elseif (! $actualEventLink->exists) {
            $data['created_at'] = now();
        }

        $actualEventLink->fill($data)->save();

        Alert::info('Ссылка сохранена.');

        return redirect()->route('platform.actual-event-links');
    }

    public function remove(ActualEventLink $actualEventLink)
    {
        $actualEventLink->delete();

        Alert::info('Ссылка удалена.');

        return redirect()->route('platform.actual-event-links');
    }
}