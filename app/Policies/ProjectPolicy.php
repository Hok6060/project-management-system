<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // An admin can view any project.
        if ($user->role === 'admin') {
            return true;
        }

        // A user can view a project if they are the owner OR the client.
        return $user->id === $project->owner_id || $user->id === $project->client_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        // FIRST, check if the project is completed. This rule applies to everyone.
        if ($project->status === 'completed') {
            return false;
        }

        // THEN, check if the user is an admin or the owner.
        return $user->role === 'admin' || $user->id === $project->owner_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // FIRST, check if the project is completed. This rule applies to everyone.
        if ($project->status === 'completed') {
            return false;
        }

        // THEN, check if the user is an admin or the owner.
        return $user->role === 'admin' || $user->id === $project->owner_id;
    }
}