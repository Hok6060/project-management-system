<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{    
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
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
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        // FIRST, deny if the project is completed.
        if ($comment->task->project->status === 'completed') {
            return false;
        }

        // THEN, check if the user is an admin or the comment author.
        return $user->role === 'admin' || $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
     {
        // FIRST, deny if the project is completed.
        if ($comment->task->project->status === 'completed') {
            return false;
        }

        // THEN, check if the user is an admin, project owner, or comment author.
        return $user->role === 'admin' || $user->id === $comment->task->project->owner_id || $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return false;
    }
}
