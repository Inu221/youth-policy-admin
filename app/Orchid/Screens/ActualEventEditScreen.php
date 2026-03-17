<?php

namespace App\Orchid\Screens;

use App\Models\ActualEvent;
use App\Models\ActualEventLink;
use App\Models\ActualEventVerification;
use App\Models\Department;
use App\Models\PlannedEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use App\Orchid\Layouts\ActualEventVerificationLayout;

class ActualEventEditScreen extends Screen
{
    public ?ActualEvent $actualEvent = null;

    public function query(ActualEvent $actualEvent): iterable
    {
        $user = auth()->user();

        // Check access for existing event
        if ($actualEvent->exists) {
            abort_unless($user->can('view', $actualEvent), 403);
        }

        // Load primary social link if exists
        $primaryLink = $actualEvent->exists
            ? $actualEvent->links()->where('link_type', 'social_post')->where('is_primary', true)->first()
            : null;

        // Pre-fill date from query parameter for new events
        if (!$actualEvent->exists && request()->has('date')) {
            $date = request()->input('date');
            try {
                $actualEvent->actual_start_at = \Carbon\Carbon::parse($date);
            } catch (\Exception $e) {
                // Invalid date, ignore
            }
        }

        return [
            'actualEvent' => $actualEvent,
            'primary_social_link' => $primaryLink?->url ?? '',
        ];
    }

    public function name(): ?string
    {
        return $this->actualEvent?->exists
            ? 'Редактирование фактического мероприятия'
            : 'Создание фактического мероприятия';
    }

    public function description(): ?string
    {
        return 'Карточка фактического мероприятия';
    }

    public function commandBar(): iterable
    {
        $user = auth()->user();
        $buttons = [];

        // Save button
        $buttons[] = Button::make('Сохранить')
            ->icon('bs.check-circle')
            ->method('save')
            ->canSee(
                ($this->actualEvent?->exists && $user->can('update', $this->actualEvent))
                || (!$this->actualEvent?->exists && $user->can('create', ActualEvent::class))
            );

        // Verification buttons (only for analyst/director and existing event)
        if ($this->actualEvent?->exists && ($user->isAnalyst() || $user->isDirector())) {
            $verification = $this->actualEvent->verification;
            $isPending = !$verification || $verification->status === ActualEventVerification::STATUS_PENDING;

            $buttons[] = Button::make('Одобрить')
                ->icon('bs.check-circle-fill')
                ->method('approve')
                ->type(\Orchid\Support\Color::SUCCESS())
                ->confirm('Подтвердить одобрение мероприятия?')
                ->canSee($isPending);

            $buttons[] = Button::make('Отклонить')
                ->icon('bs.x-circle-fill')
                ->method('reject')
                ->type(\Orchid\Support\Color::DANGER())
                ->confirm('Подтвердить отклонение мероприятия?')
                ->canSee($isPending);
        }

        // Delete button
        $buttons[] = Button::make('Удалить')
            ->icon('bs.trash3')
            ->method('remove')
            ->canSee($this->actualEvent?->exists && $user->can('delete', $this->actualEvent));

        return $buttons;
    }

    public function layout(): iterable
    {
        $user = auth()->user();

        $layouts = [
            Layout::rows([
                Relation::make('actualEvent.department_id')
                    ->title('Подразделение')
                    ->fromModel(Department::class, 'name')
                    ->applyScope('forUser', $user)
                    ->disabled($user->isDepartmentHead())
                    ->value($user->isDepartmentHead() ? $user->department_id : null)
                    ->required(),

                Relation::make('actualEvent.planned_event_id')
                    ->title('Плановое мероприятие')
                    ->fromModel(PlannedEvent::class, 'title')
                    ->applyScope('forUser', $user)
                    ->help('Можно оставить пустым для внепланового мероприятия'),

                Input::make('actualEvent.title')
                    ->title('Название мероприятия')
                    ->required(),

                Input::make('actualEvent.description')
                    ->title('Описание'),

                DateTimer::make('actualEvent.actual_start_at')
                    ->title('Дата и время начала')
                    ->enableTime()
                    ->required(),

                DateTimer::make('actualEvent.actual_end_at')
                    ->title('Дата и время окончания')
                    ->enableTime(),

                Input::make('actualEvent.location_name')
                    ->title('Место проведения'),

                Input::make('actualEvent.location_url')
                    ->title('Ссылка на место / гео-ссылка'),

                Input::make('primary_social_link')
                    ->title('Ссылка на пост в соцсети')
                    ->type('url')
                    ->help('Обязательное поле: ссылка на публикацию о мероприятии в социальных сетях (ВК, Telegram, и т.д.)')
                    ->placeholder('https://vk.com/wall-123456789_123')
                    ->required(),

                Relation::make('actualEvent.responsible_user_id')
                    ->title('Ответственный')
                    ->fromModel(User::class, 'full_name')
                    ->required(),

                Input::make('actualEvent.planned_participants_snapshot')
                    ->title('Плановое количество участников (snapshot)')
                    ->type('number'),

                Input::make('actualEvent.actual_participants_count')
                    ->title('Фактическое количество участников')
                    ->type('number')
                    ->required(),

                Select::make('actualEvent.status')
                    ->title('Статус')
                    ->options([
                        ActualEvent::STATUS_PLANNED => 'Запланировано',
                        ActualEvent::STATUS_IN_PROGRESS => 'Проводится',
                        ActualEvent::STATUS_ARCHIVED => 'Архив',
                        ActualEvent::STATUS_CANCELLED => 'Отменено',
                    ])
                    ->required(),
            ]),
        ];

        // Add verification layout for existing events (analyst/director only)
        if ($this->actualEvent?->exists && ($user->isAnalyst() || $user->isDirector())) {
            $layouts[] = ActualEventVerificationLayout::class;
        }

        return $layouts;
    }

    public function save(ActualEvent $actualEvent, Request $request)
    {
        $user = auth()->user();

        // Check permissions
        if ($actualEvent->exists) {
            abort_unless($user->can('update', $actualEvent), 403);
        } else {
            abort_unless($user->can('create', ActualEvent::class), 403);
        }

        $validated = $request->validate([
            'actualEvent.department_id' => ['required', 'integer', 'exists:departments,id'],
            'actualEvent.planned_event_id' => ['nullable', 'integer', 'exists:planned_events,id'],
            'actualEvent.title' => ['required', 'string', 'max:255'],
            'actualEvent.description' => ['nullable', 'string'],
            'actualEvent.actual_start_at' => ['required', 'date'],
            'actualEvent.actual_end_at' => ['nullable', 'date', 'after_or_equal:actualEvent.actual_start_at'],
            'actualEvent.location_name' => ['nullable', 'string', 'max:255'],
            'actualEvent.location_url' => ['nullable', 'string', 'max:1000'],
            'primary_social_link' => ['required', 'url', 'max:1000'],
            'actualEvent.responsible_user_id' => ['required', 'integer', 'exists:users,id'],
            'actualEvent.planned_participants_snapshot' => ['nullable', 'integer', 'min:0'],
            'actualEvent.actual_participants_count' => ['required', 'integer', 'min:0'],
            'actualEvent.status' => ['required', 'in:planned,in_progress,archived,cancelled'],
        ]);

        $data = $validated['actualEvent'];

        // Force department_id for department_head
        if ($user->isDepartmentHead()) {
            $data['department_id'] = $user->department_id;
        }

        if (! $actualEvent->exists) {
            $data['created_by'] = auth()->id();
        } else {
            $data['updated_by'] = auth()->id();
        }

        if ($data['status'] === ActualEvent::STATUS_ARCHIVED) {
            $data['completed_at'] = now();
        } elseif (($data['status'] ?? null) !== ActualEvent::STATUS_ARCHIVED) {
            $data['completed_at'] = null;
        }

        if (!empty($data['planned_event_id']) && empty($data['planned_participants_snapshot'])) {
            $plannedEvent = PlannedEvent::find($data['planned_event_id']);
            if ($plannedEvent) {
                $data['planned_participants_snapshot'] = $plannedEvent->planned_participants_count;
            }
        }

        $actualEvent->fill($data)->save();

        // Save/update primary social link
        $socialLinkUrl = $validated['primary_social_link'];
        $existingLink = $actualEvent->links()
            ->where('link_type', 'social_post')
            ->where('is_primary', true)
            ->first();

        if ($existingLink) {
            $existingLink->update(['url' => $socialLinkUrl]);
        } else {
            $actualEvent->links()->create([
                'link_type' => 'social_post',
                'url' => $socialLinkUrl,
                'is_primary' => true,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
        }

        Alert::info('Фактическое мероприятие сохранено.');

        return redirect()->route('platform.actual-events');
    }

    public function approve(ActualEvent $actualEvent, Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isAnalyst() || $user->isDirector(), 403);

        $comment = $request->input('verification_new_comment');

        $verification = $actualEvent->verification;
        if ($verification) {
            $verification->update([
                'status' => ActualEventVerification::STATUS_APPROVED,
                'reviewer_id' => auth()->id(),
                'reviewed_at' => now(),
                'comment' => $comment,
            ]);
        } else {
            ActualEventVerification::create([
                'actual_event_id' => $actualEvent->id,
                'status' => ActualEventVerification::STATUS_APPROVED,
                'reviewer_id' => auth()->id(),
                'reviewed_at' => now(),
                'comment' => $comment,
            ]);
        }

        Alert::success('Мероприятие одобрено.');

        return redirect()->route('platform.actual-events');
    }

    public function reject(ActualEvent $actualEvent, Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isAnalyst() || $user->isDirector(), 403);

        $comment = $request->input('verification_new_comment');

        $verification = $actualEvent->verification;
        if ($verification) {
            $verification->update([
                'status' => ActualEventVerification::STATUS_REJECTED,
                'reviewer_id' => auth()->id(),
                'reviewed_at' => now(),
                'comment' => $comment,
            ]);
        } else {
            ActualEventVerification::create([
                'actual_event_id' => $actualEvent->id,
                'status' => ActualEventVerification::STATUS_REJECTED,
                'reviewer_id' => auth()->id(),
                'reviewed_at' => now(),
                'comment' => $comment,
            ]);
        }

        Alert::warning('Мероприятие отклонено.');

        return redirect()->route('platform.actual-events');
    }

    public function remove(ActualEvent $actualEvent)
    {
        abort_unless(auth()->user()->can('delete', $actualEvent), 403);

        $actualEvent->delete();

        Alert::info('Фактическое мероприятие удалено.');

        return redirect()->route('platform.actual-events');
    }
}