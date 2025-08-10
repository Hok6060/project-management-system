<?php

namespace App\ProjectManagement\Observers;

use App\Models\User;
use App\ProjectManagement\Models\Project;
use App\ProjectManagement\Notifications\ProjectStatusUpdated; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        $this->recordActivity($project, 'created the project');
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        $before = $project->getOriginal();

        // If the status has changed, notify the relevant users.
        if ($project->isDirty('status')) {
            $originalStatus = $before['status'];

            $projectOwner = $project->owner;
            $client = $project->client;

            $usersToNotify = collect();

            // Add the project owner if they didn't make the change
            if ($projectOwner && $projectOwner->id !== Auth::id()) {
                $usersToNotify->push($projectOwner);
            }

            // Add the client if they exist and didn't make the change
            if ($client && $client->id !== Auth::id()) {
                $usersToNotify->push($client);
            }

            // Send the notification
            foreach ($usersToNotify->unique('id') as $user) {
                $user->notify(new ProjectStatusUpdated($project, $originalStatus));
            }
        }

        foreach ($project->getChanges() as $attribute => $after) {
            if ($attribute === 'updated_at') {
                continue;
            }

            $description = $this->formatUpdateDescription($attribute, $before, $project);
            $this->recordActivity($project, $description);
        }
    }

    /**
     * Handle the Project "deleting" event.
     * This runs just before the project is deleted.
     */
    public function deleting(Project $project): void
    {
        // Loop through each task in the project
        foreach ($project->tasks as $task) {
            // Loop through each attachment on the task and delete its file
            foreach ($task->attachments as $attachment) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        }
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        //
    }

    /**
     * Formats the description for a project update activity.
     */
    protected function formatUpdateDescription(string $attribute, array $before, Project $project): string
    {
        $after = $project->$attribute;

        switch ($attribute) {
            case 'name':
                return "renamed the project from {$before['name']} to {$project->name}";
            case 'description':
                return "updated the project description";
            case 'status':
                return "changed the project status from " . ucwords(str_replace('_', ' ', $before['status'])) . " to " . ucwords(str_replace('_', ' ', $after)) . "";
            case 'start_date':
                $oldDate = $before['start_date'] ? date('Y-m-d', strtotime($before['start_date'])) : 'N/A';
                $newDate = $after ? date('Y-m-d', strtotime($after)) : 'N/A';
                return "changed the start date from {$oldDate} to {$newDate}";
            case 'end_date':
                $oldDate = $before['end_date'] ? date('Y-m-d', strtotime($before['end_date'])) : 'N/A';
                $newDate = $after ? date('Y-m-d', strtotime($after)) : 'N/A';
                return "changed the end date from {$oldDate} to {$newDate}";
            case 'budget':
                $oldBudget = number_format((float)$before['budget'], 2);
                $newBudget = number_format((float)$after, 2);
                return "updated the project budget from \${$oldBudget} to \${$newBudget}";
            case 'owner_id':
                $oldOwner = User::find($before['owner_id']);
                $newOwner = User::find($after);
                return "changed the project owner from " . ($oldOwner->name ?? 'Unassigned') . " to " . ($newOwner->name ?? 'Unassigned');
            case 'client_id':
                $oldClient = User::find($before['client_id']);
                $newClient = User::find($after);
                return "changed the client from " . ($oldClient->name ?? 'N/A') . " to " . ($newClient->name ?? 'N/A');
            default:
                return "updated the {$attribute} from '" . str_replace('_', ' ', $before[$attribute]) . "' to '" . str_replace('_', ' ', $after) . "'";
        }
    }

    /**
     * A helper method to record activity for a project.
     */
    protected function recordActivity(Project $project, string $description)
    {
        if (Auth::check()) {
            $project->activities()->create([
                'user_id' => Auth::id(),
                'description' => $description,
            ]);
        }
    }
}
