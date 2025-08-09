<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskStatusUpdated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        $this->recordActivity($task, "created the task \"{$task->name}\"");

        // If the task was created with an assignee, notify them.
        if ($task->assignee) {
            $task->assignee->notify(new TaskAssigned($task));
        }
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        $before = $task->getOriginal();

        // If the status has changed, notify the relevant users.
        if ($task->isDirty('status')) {
            $originalStatus = $before['status'];

            $projectOwner = $task->project->owner;
            $taskAssignee = $task->assignee;

            $usersToNotify = collect();

            // Add the project owner if they didn't make the change
            if ($projectOwner && $projectOwner->id !== Auth::id()) {
                $usersToNotify->push($projectOwner);
            }

            // Add the task assignee if they exist and didn't make the change
            if ($taskAssignee && $taskAssignee->id !== Auth::id()) {
                $usersToNotify->push($taskAssignee);
            }

            // Send the notification
            foreach ($usersToNotify->unique('id') as $user) {
                $user->notify(new TaskStatusUpdated($task, $originalStatus));
            }
        }

        // If the assignee has changed, notify the new user.
        if ($task->isDirty('assignee_id') && $task->assignee) {
            $task->assignee->notify(new TaskAssigned($task));
        }

        foreach ($task->getChanges() as $attribute => $after) {
            if ($attribute === 'updated_at') {
                continue;
            }

            $description = $this->formatUpdateDescription($attribute, $before, $task);
            $this->recordActivity($task, $description);
        }
    }

    /**
     * Handle the Task "deleting" event.
     */
    public function deleting(Task $task): void
    {
        foreach ($task->attachments as $attachment) {
            Storage::disk('public')->delete($attachment->file_path);
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        $this->recordActivity($task, "deleted the task \"{$task->name}\"");
    }

    /**
     * Formats the description for an update activity.
     */
    protected function formatUpdateDescription(string $attribute, array $before, Task $task): string
    {
        $after = $task->$attribute;

        switch ($attribute) {
            case 'name':
                return "renamed the task from \"{$before['name']}\" to \"{$task->name}\"";
            case 'description':
                return "updated the description of \"{$task->name}\"";
            case 'assignee_id':
                $oldAssignee = User::find($before['assignee_id']);
                $newAssignee = User::find($after);
                return "changed the assignee of \"{$task->name}\" from " . ($oldAssignee->name ?? 'Unassigned') . " to " . ($newAssignee->name ?? 'Unassigned');
            case 'due_date':
                $oldDate = $before['due_date'] ? date('Y-m-d', strtotime($before['due_date'])) : 'N/A';
                $newDate = $after ? date('Y-m-d', strtotime($after)) : 'N/A';
                return "changed the due date of \"{$task->name}\" from {$oldDate} to {$newDate}";
            default:
                return "updated the {$attribute} of \"{$task->name}\" from " . str_replace('_', ' ', $before[$attribute]) . " to " . str_replace('_', ' ', $after);
        }
    }

    /**
     * A helper method to record activity for a task.
     */
    protected function recordActivity(Task $task, string $description)
    {
        if (Auth::check()) {
            $task->project->activities()->create([
                'user_id' => Auth::id(),
                'description' => $description,
            ]);
        }
    }
}
