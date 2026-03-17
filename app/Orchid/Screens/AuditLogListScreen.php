<?php

namespace App\Orchid\Screens;

use App\Models\AuditLog;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\TD;

class AuditLogListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $user = auth()->user();

        // Only director can view audit logs
        abort_unless($user->isDirector(), 403);

        return [
            'auditLogs' => AuditLog::with('user')
                ->orderByDesc('created_at')
                ->paginate(50),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Журнал действий';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Аудит всех критических операций в системе';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('auditLogs', [
                TD::make('created_at', 'Дата и время')
                    ->render(function (AuditLog $log) {
                        return $log->created_at->format('d.m.Y H:i:s');
                    })
                    ->sort(),

                TD::make('user', 'Пользователь')
                    ->render(function (AuditLog $log) {
                        return $log->user?->full_name ?? $log->user?->name ?? 'Система';
                    }),

                TD::make('action', 'Действие')
                    ->render(function (AuditLog $log) {
                        $badge = match ($log->action) {
                            'created' => '<span class="badge bg-success">Создание</span>',
                            'updated' => '<span class="badge bg-info">Обновление</span>',
                            'deleted' => '<span class="badge bg-danger">Удаление</span>',
                            default => '<span class="badge bg-secondary">' . e($log->action) . '</span>',
                        };
                        return $badge;
                    }),

                TD::make('entity_type', 'Сущность')
                    ->render(function (AuditLog $log) {
                        $type = class_basename($log->entity_type);
                        return match ($type) {
                            'ActualEvent' => 'Мероприятие',
                            'AnnualPlan' => 'Годовой план',
                            'Department' => 'Подразделение',
                            'User' => 'Пользователь',
                            default => $type,
                        };
                    }),

                TD::make('description', 'Описание')
                    ->render(function (AuditLog $log) {
                        return e($log->description);
                    }),

                TD::make('changes', 'Изменения')
                    ->render(function (AuditLog $log) {
                        if ($log->action === 'created' && $log->new_values) {
                            return '<small class="text-muted">+' . count($log->new_values) . ' полей</small>';
                        }
                        if ($log->action === 'updated' && $log->old_values && $log->new_values) {
                            $changes = array_keys($log->new_values);
                            return '<small class="text-muted">Изм.: ' . implode(', ', $changes) . '</small>';
                        }
                        if ($log->action === 'deleted' && $log->old_values) {
                            return '<small class="text-muted">-' . count($log->old_values) . ' полей</small>';
                        }
                        return '—';
                    }),

                TD::make('ip_address', 'IP')
                    ->render(function (AuditLog $log) {
                        return $log->ip_address ? '<small class="text-muted">' . e($log->ip_address) . '</small>' : '—';
                    }),
            ]),
        ];
    }
}
