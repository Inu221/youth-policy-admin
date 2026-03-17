<?php

namespace App\Observers;

use App\Models\AnnualPlan;
use App\Traits\Auditable;

class AnnualPlanObserver
{
    use Auditable;

    /**
     * Handle the AnnualPlan "created" event.
     */
    public function created(AnnualPlan $annualPlan): void
    {
        $newValues = $annualPlan->only([
            'title',
            'year',
            'department_id',
            'status',
        ]);

        self::logAudit(
            'created',
            $annualPlan,
            null,
            $newValues,
            "Создан план: {$annualPlan->title}"
        );
    }

    /**
     * Handle the AnnualPlan "updated" event.
     */
    public function updated(AnnualPlan $annualPlan): void
    {
        $changes = $annualPlan->getChanges();

        // Skip if no meaningful changes
        $meaningfulChanges = array_diff_key($changes, array_flip(['updated_at']));
        if (empty($meaningfulChanges)) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        foreach ($meaningfulChanges as $key => $newValue) {
            $oldValues[$key] = $annualPlan->getOriginal($key);
            $newValues[$key] = $newValue;
        }

        // Special logging for status changes
        if (isset($changes['status'])) {
            $description = "Изменен статус плана '{$annualPlan->title}': {$oldValues['status']} → {$newValues['status']}";
        } elseif (isset($changes['approved_by'])) {
            $description = "План '{$annualPlan->title}' одобрен";
        } else {
            $description = "Обновлен план: {$annualPlan->title}";
        }

        self::logAudit(
            'updated',
            $annualPlan,
            $oldValues,
            $newValues,
            $description
        );
    }

    /**
     * Handle the AnnualPlan "deleted" event.
     */
    public function deleted(AnnualPlan $annualPlan): void
    {
        $oldValues = $annualPlan->only([
            'title',
            'year',
            'department_id',
            'status',
        ]);

        self::logAudit(
            'deleted',
            $annualPlan,
            $oldValues,
            null,
            "Удален план: {$annualPlan->title}"
        );
    }
}
