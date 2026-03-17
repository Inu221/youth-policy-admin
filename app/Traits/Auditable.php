<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    /**
     * Log an audit entry.
     */
    protected static function logAudit(
        string $action,
        $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): void {
        // Skip if not in HTTP context
        if (!app()->runningInConsole() && !request()) {
            return;
        }

        $entityType = get_class($model);
        $entityId = $model->id ?? null;

        // Get user ID
        $userId = auth()->check() ? auth()->id() : null;

        // Get IP and User Agent
        $ipAddress = request()?->ip();
        $userAgent = request()?->userAgent();

        AuditLog::create([
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description ?? "{$action} {$entityType}",
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    /**
     * Get changed attributes for audit logging.
     */
    protected static function getChangedAttributes($model): array
    {
        $changes = [];

        if (method_exists($model, 'getDirty')) {
            $dirty = $model->getDirty();

            foreach ($dirty as $key => $value) {
                // Skip certain fields
                if (in_array($key, ['updated_at', 'created_at', 'remember_token'])) {
                    continue;
                }

                $changes[$key] = $value;
            }
        }

        return $changes;
    }
}
