<?php

namespace App\Orchid\Screens;

use App\Models\ActualEvent;
use App\Models\ActualEventFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ActualEventFileEditScreen extends Screen
{
    public ?ActualEventFile $file = null;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(ActualEventFile $file): iterable
    {
        return [
            'file' => $file,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->file?->exists
            ? 'Просмотр файла'
            : 'Загрузка файла';
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
            Button::make('Загрузить')
                ->icon('bs.cloud-upload')
                ->method('save')
                ->canSee(!$this->file?->exists),

            Button::make('Удалить')
                ->icon('bs.trash3')
                ->method('remove')
                ->confirm('Вы уверены, что хотите удалить этот файл?')
                ->canSee($this->file?->exists && ($user->isDirector() || $user->isDepartmentHead())),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $user = auth()->user();

        if ($this->file?->exists) {
            return [
                Layout::rows([
                    Relation::make('file.actual_event_id')
                        ->title('Мероприятие')
                        ->fromModel(ActualEvent::class, 'title')
                        ->disabled(),
                ]),
            ];
        }

        return [
            Layout::rows([
                Relation::make('actual_event_id')
                    ->title('Мероприятие')
                    ->fromModel(ActualEvent::class, 'title')
                    ->applyScope('forUser', $user)
                    ->required()
                    ->help('Выберите мероприятие для загрузки файла'),

                Upload::make('file')
                    ->title('Файл')
                    ->maxFiles(1)
                    ->acceptedFiles('.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.rar')
                    ->help('Допустимые форматы: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, ZIP, RAR. Максимальный размер: 10 МБ')
                    ->required(),
            ]),
        ];
    }

    public function save(Request $request)
    {
        $user = auth()->user();

        abort_unless($user->isDirector() || $user->isDepartmentHead(), 403);

        $validated = $request->validate([
            'actual_event_id' => ['required', 'exists:actual_events,id'],
            'file' => ['required', 'file', 'max:10240'], // 10MB
        ]);

        $actualEvent = ActualEvent::findOrFail($validated['actual_event_id']);

        // Check if user can upload to this event
        if ($user->isDepartmentHead() && $actualEvent->department_id !== $user->department_id) {
            abort(403, 'You cannot upload files to events from other departments');
        }

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $storedName = time() . '_' . $originalName;
        $path = $uploadedFile->storeAs('event_files', $storedName, 'public');

        ActualEventFile::create([
            'actual_event_id' => $validated['actual_event_id'],
            'stored_name' => $storedName,
            'original_name' => $originalName,
            'file_path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'file_size' => $uploadedFile->getSize(),
            'uploaded_by' => $user->id,
            'created_at' => now(),
        ]);

        Alert::success('Файл успешно загружен.');

        return redirect()->route('platform.actual-event-files');
    }

    public function remove(ActualEventFile $file)
    {
        $user = auth()->user();

        abort_unless($user->isDirector() || $user->isDepartmentHead(), 403);

        // Check department access
        if ($user->isDepartmentHead() && $file->actualEvent->department_id !== $user->department_id) {
            abort(403);
        }

        // Delete physical file
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        Alert::info('Файл удален.');

        return redirect()->route('platform.actual-event-files');
    }
}
