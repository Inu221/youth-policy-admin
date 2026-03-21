<?php

namespace App\Policies;

use App\Models\AnnualPlan;
use App\Models\PlannedEvent;
use App\Models\User;

class PlannedEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isDirector() || $user->isAnalyst() || $user->isDepartmentHead();
    }

    public function view(User $user, PlannedEvent $plannedEvent): bool
    {
        if ($user->isDirector() || $user->isAnalyst()) {
            return true;
        }

        if ($user->isDepartmentHead()) {
            return $plannedEvent->annualPlan?->department_id === $user->department_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isDirector() || $user->isDepartmentHead();
    }

    public function update(User $user, PlannedEvent $plannedEvent): bool
    {
        if ($user->isDirector()) {
            return true;
        }

        if ($user->isDepartmentHead()) {
            return $plannedEvent->annualPlan?->department_id === $user->department_id
                && $plannedEvent->annualPlan?->status === AnnualPlan::STATUS_DRAFT;
        }

        return false;
    }

    public function delete(User $user, PlannedEvent $plannedEvent): bool
    {
        return $this->update($user, $plannedEvent);
    }

    public function restore(User $user, PlannedEvent $plannedEvent): bool
    {
        return $user->isDirector();
    }

    public function forceDelete(User $user, PlannedEvent $plannedEvent): bool
    {
        return $user->isDirector();
    }
}
