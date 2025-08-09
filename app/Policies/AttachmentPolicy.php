<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AttachmentPolicy
{
    /**
     * Determine whether the user can create attachments for a task.
     */
    public function create(User $user, Task $task): bool
    {
        // FIRST, deny if the project is completed.
        if ($task->project->status === 'completed') {
            return false;
        }

        // THEN, check if the user is an admin or part of the project.
        return $user->role === 'admin' || $user->id === $task->project->owner_id || $user->id === $task->project->client_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        // FIRST, deny if the project is completed.
        if ($attachment->task->project->status === 'completed') {
            return false;
        }

        // THEN, check if the user is an admin, project owner, or the uploader.
        return $user->role === 'admin' || $user->id === $attachment->task->project->owner_id || $user->id === $attachment->user_id;
    }
}
