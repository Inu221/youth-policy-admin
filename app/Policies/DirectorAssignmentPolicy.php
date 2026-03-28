<?php

namespace App\Policies;

use App\Models\DirectorAssignment;
use App\Models\User;

class DirectorAssignmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isDirector() || $user->isDepartmentHead();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DirectorAssignment $assignment): bool
    {
        if ($user->isDirector()) {
            return true;
        }

        if ($user->isDepartmentHead()) {
            return $user->department_id === $assignment->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isDirector();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DirectorAssignment $assignment): bool
    {
        // Директор может редактировать всё
        if ($user->isDirector()) {
            return true;
        }

        // Начальник отдела может менять только статус своих поручений
        if ($user->isDepartmentHead()) {
            return $user->department_id === $assignment->department_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DirectorAssignment $assignment): bool
    {
        return $user->isDirector();
    }

    /**
     * Determine whether the user can add comments to the model.
     */
    public function addComment(User $user, DirectorAssignment $assignment): bool
    {
        if ($user->isDirector()) {
            return true;
        }

        if ($user->isDepartmentHead()) {
            return $user->department_id === $assignment->department_id;
        }

        return false;
    }
}
