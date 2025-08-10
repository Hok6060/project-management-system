<?php

namespace App\ProjectManagement\Observers;

use App\ProjectManagement\Models\Attachment;
use App\ProjectManagement\Notifications\NewAttachment;
use Illuminate\Support\Facades\Auth;

class AttachmentObserver
{
    /**
     * Handle the Attachment "created" event.
     */
    public function created(Attachment $attachment): void
    {
        $description = "attached the file \"{$attachment->file_name}\" to the task \"{$attachment->task->name}\"";

        $attachment->task->project->activities()->create([
            'user_id' => Auth::id(),
            'description' => $description,
        ]);

        $projectOwner = $attachment->task->project->owner;
        $taskAssignee = $attachment->task->assignee;

        $usersToNotify = collect();

        // Add the project owner if they didn't upload the file
        if ($projectOwner && $projectOwner->id !== Auth::id()) {
            $usersToNotify->push($projectOwner);
        }

        // Add the task assignee if they exist and didn't upload the file
        if ($taskAssignee && $taskAssignee->id !== Auth::id()) {
            $usersToNotify->push($taskAssignee);
        }

        // Send the notification
        foreach ($usersToNotify->unique('id') as $user) {
            $user->notify(new NewAttachment($attachment));
        }
    }

    /**
     * Handle the Attachment "deleted" event.
     */
    public function deleted(Attachment $attachment): void
    {
        $description = "deleted the file \"{$attachment->file_name}\" from the task \"{$attachment->task->name}\"";

        $attachment->task->project->activities()->create([
            'user_id' => Auth::id(),
            'description' => $description,
        ]);
    }
}