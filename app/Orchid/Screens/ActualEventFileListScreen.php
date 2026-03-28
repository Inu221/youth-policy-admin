<?php

namespace App\Orchid\Screens;

use App\Models\ActualEventFile;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ActualEventFileListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $user = auth()->user();

        $filesQuery = ActualEventFile::with(['actualEvent.department', 'uploader'])
            ->filters();

        // Filter by department for department_head
        if ($user->isDepartmentHead()) {
            $filesQuery->whereHas('actualEvent', function ($q) use ($user) {
                $q->where('department_id', $user->department_id);
            });
        }

        return [
            'files' => $filesQuery
                ->defaultSort('created_at', 'desc')
                ->orderByDesc('id')
                ->paginate(20),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Файлы мероприятий';
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
            Link::make('Загрузить файл')
                ->icon('bs.cloud-upload')
                ->route('platform.actual-event-files.create')
                ->canSee($user->isDirector() || $user->isDepartmentHead()),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('files', [
                TD::make('id', 'ID')
                    ->sort(),

                TD::make('actual_event_id', 'Мероприятие')
                    ->sort()
                    ->render(function (ActualEventFile $file) {
                        return Link::make($file->actualEvent->title)
                            ->route('platform.actual-events.edit', $file->actualEvent);
                    }),

                TD::make('original_name', 'Файл')
                    ->sort()
                    ->render(function (ActualEventFile $file) {
                        return Link::make($file->original_name)
                            ->href(route('platform.actual-event-files.download', $file));
                    }),

                TD::make('file_size', 'Размер')
                    ->sort()
                    ->render(fn (ActualEventFile $file) => $this->formatFileSize($file->file_size)),

                TD::make('uploaded_by', 'Загрузил')
                    ->sort()
                    ->render(fn (ActualEventFile $file) => $file->uploader?->full_name ?? '—'),

                TD::make('created_at', 'Дата загрузки')
                    ->sort()
                    ->render(fn (ActualEventFile $file) => $file->created_at?->format('d.m.Y H:i') ?? '—'),
            ]),
        ];
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' МБ';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' КБ';
        }
        return $bytes . ' байт';
    }
}
