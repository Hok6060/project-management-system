<?php

namespace App\ProjectManagement\Policies;

use App\Models\User;
use App\ProjectManagement\Models\Task;
use App\ProjectManagement\Models\Project;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can create models.
     *
     * A user can create a task if they are part of the project.
     */
    public function create(User $user, \App\ProjectManagement\Models\Project $project): bool
    {
        // Deny if the project is completed
        if ($project->status === 'completed') {
            return false;
        }

        return $user->id === $project->owner_id || $user->id === $project->client_id;
    }

    /**
     * Determine whether the user can update the model.
     *
     * A user can update a task if they own the project OR if the task is assigned to them.
     */
    public function update(User $user, Task $task): bool
    {
        // Deny if the project is completed
        if ($task->project->status === 'completed') {
            return false;
        }

        return $user->id === $task->project->owner_id || $user->id === $task->assignee_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * Only the project owner can delete a task.
     */
    public function delete(User $user, Task $task): bool
    {
        // Deny if the project is completed
        if ($task->project->status === 'completed') {
            return false;
        }

        return $user->id === $task->project->owner_id;
    }
}
