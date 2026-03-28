<?php

namespace App\Orchid\Screens;

use App\Models\Department;
use App\Models\DirectorAssignment;
use App\Models\DirectorAssignmentComment;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class DirectorAssignmentEditScreen extends Screen
{
    public ?DirectorAssignment $assignment = null;

    public function query(DirectorAssignment $assignment): iterable
    {
        $assignment->load(['department', 'creator', 'comments.user']);

        return [
            'assignment' => $assignment,
            'comments' => $assignment->comments,
        ];
    }

    public function name(): ?string
    {
        return $this->assignment?->exists
            ? 'Редактирование поручения'
            : 'Создание поручения';
    }

    public function description(): ?string
    {
        return 'Карточка поручения';
    }

    public function commandBar(): iterable
    {
        $user = auth()->user();

        return [
            Button::make('Сохранить')
                ->icon('bs.check-circle')
                ->method('save')
                ->canSee($user->can('update', $this->assignment ?? new DirectorAssignment)),

            Button::make('Удалить')
                ->icon('bs.trash3')
                ->method('remove')
                ->canSee($this->assignment?->exists && $user->can('delete', $this->assignment))
                ->confirm('Вы уверены, что хотите удалить это поручение?'),
        ];
    }

    public function layout(): iterable
    {
        $user = auth()->user();
        $isDirector = $user->isDirector();
        $isDepartmentHead = $user->isDepartmentHead();

        $mainFields = [
            Input::make('assignment.title')
                ->title('Название поручения')
                ->required()
                ->disabled(!$isDirector)
                ->placeholder('Например: Подготовить отчет по итогам квартала'),

            TextArea::make('assignment.description')
                ->title('Описание')
                ->rows(5)
                ->disabled(!$isDirector)
                ->placeholder('Подробное описание поручения'),

            Relation::make('assignment.department_id')
                ->title('Подразделение')
                ->fromModel(Department::class, 'name')
                ->required()
                ->disabled(!$isDirector || $this->assignment?->exists)
                ->help($this->assignment?->exists
                    ? 'Подразделение нельзя изменить после создания'
                    : 'Выберите подразделение для поручения'),

            DateTimer::make('assignment.due_date')
                ->title('Срок исполнения')
                ->format('Y-m-d')
                ->disabled(!$isDirector)
                ->allowInput(),

            Select::make('assignment.status')
                ->title('Статус')
                ->options([
                    DirectorAssignment::STATUS_PENDING => 'Ожидает',
                    DirectorAssignment::STATUS_IN_PROGRESS => 'В работе',
                    DirectorAssignment::STATUS_COMPLETED => 'Выполнено',
                ])
                ->required()
                ->help($isDepartmentHead
                    ? 'Вы можете изменить статус поручения'
                    : ''),
        ];

        $layouts = [
            Layout::rows($mainFields),
        ];

        // Блок комментариев (только для существующего поручения)
        if ($this->assignment?->exists) {
            $layouts[] = Layout::view('platform.director-assignment-comments', [
                'assignment' => $this->assignment,
                'comments' => $this->query($this->assignment)['comments'],
                'canComment' => $user->can('addComment', $this->assignment),
            ]);
        }

        return $layouts;
    }

    public function save(DirectorAssignment $assignment, Request $request)
    {
        $this->authorize('update', $assignment->exists ? $assignment : new DirectorAssignment);

        $user = auth()->user();
        $rules = [
            'assignment.title' => ['required', 'string', 'max:255'],
            'assignment.description' => ['nullable', 'string'],
            'assignment.department_id' => ['required', 'integer', 'exists:departments,id'],
            'assignment.status' => ['required', 'in:pending,in_progress,completed'],
            'assignment.due_date' => ['nullable', 'date'],
        ];

        $validated = $request->validate($rules);
        $data = $validated['assignment'];

        // Начальник отдела может менять только статус
        if ($user->isDepartmentHead()) {
            $data = ['status' => $data['status']];
        }

        if (!$assignment->exists) {
            $data['created_by'] = $user->id;
        }

        $assignment->fill($data)->save();

        Alert::info('Поручение сохранено.');

        return redirect()->route('platform.director-assignments');
    }

    public function addComment(DirectorAssignment $assignment, Request $request)
    {
        $this->authorize('addComment', $assignment);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        DirectorAssignmentComment::create([
            'director_assignment_id' => $assignment->id,
            'comment' => $validated['comment'],
            'user_id' => auth()->id(),
        ]);

        Alert::info('Комментарий добавлен.');

        return redirect()->route('platform.director-assignments.edit', $assignment);
    }

    public function remove(DirectorAssignment $assignment)
    {
        $this->authorize('delete', $assignment);

        $assignment->delete();

        Alert::info('Поручение удалено.');

        return redirect()->route('platform.director-assignments');
    }
}
