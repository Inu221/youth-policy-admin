<?php

namespace App\Policies;

use App\Models\ActualEvent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ActualEventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ActualEvent $actualEvent): bool
    {
        if ($user->isDirector() || $user->isAnalyst()) {
            return true;
        }

        if ($user->isDepartmentHead()) {
            return $user->department_id === $actualEvent->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isDirector() || $user->isDepartmentHead();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ActualEvent $actualEvent): bool
    {
        if ($user->isDirector()) {
            return true;
        }

        if ($user->isDepartmentHead()) {
            return $user->department_id === $actualEvent->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ActualEvent $actualEvent): bool
    {
        if ($user->isDirector()) {
            return true;
        }

        if ($user->isDepartmentHead()) {
            return $user->department_id === $actualEvent->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ActualEvent $actualEvent): bool
    {
        return $user->isDirector();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ActualEvent $actualEvent): bool
    {
        return $user->isDirector();
    }
}
