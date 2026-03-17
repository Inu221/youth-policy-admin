<?php

namespace App\Observers;

use App\Models\ActualEvent;
use App\Traits\Auditable;

class ActualEventObserver
{
    use Auditable;

    /**
     * Handle the ActualEvent "created" event.
     */
    public function created(ActualEvent $actualEvent): void
    {
        $newValues = $actualEvent->only([
            'title',
            'department_id',
            'status',
            'actual_start_at',
            'actual_participants_count',
        ]);

        self::logAudit(
            'created',
            $actualEvent,
            null,
            $newValues,
            "Создано мероприятие: {$actualEvent->title}"
        );
    }

    /**
     * Handle the ActualEvent "updated" event.
     */
    public function updated(ActualEvent $actualEvent): void
    {
        $changes = $actualEvent->getChanges();

        // Skip if no meaningful changes
        $meaningfulChanges = array_diff_key($changes, array_flip(['updated_at', 'updated_by']));
        if (empty($meaningfulChanges)) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach ($meaningfulChanges as $key => $newValue) {
            $oldValues[$key] = $actualEvent->getOriginal($key);
            $newValues[$key] = $newValue;
        }

        // Special logging for status changes
        if (isset($changes['status'])) {
            $description = "Изменен статус мероприятия '{$actualEvent->title}': {$oldValues['status']} → {$newValues['status']}";
        } else {
            $description = "Обновлено мероприятие: {$actualEvent->title}";
        }

        self::logAudit(
            'updated',
            $actualEvent,
            $oldValues,
            $newValues,
            $description
        );
    }

    /**
     * Handle the ActualEvent "deleted" event.
     */
    public function deleted(ActualEvent $actualEvent): void
    {
        $oldValues = $actualEvent->only([
            'title',
            'department_id',
            'status',
            'actual_start_at',
        ]);

        self::logAudit(
            'deleted',
            $actualEvent,
            $oldValues,
            null,
            "Удалено мероприятие: {$actualEvent->title}"
        );
    }
}
