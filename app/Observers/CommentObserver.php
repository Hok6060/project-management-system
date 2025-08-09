<?php

namespace App\Observers;

use App\Models\Comment;
use App\Notifications\NewComment;
use Illuminate\Support\Facades\Auth;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        $description = "added a comment to the task \"{$comment->task->name}\"";
        $comment->task->project->activities()->create([
            'user_id' => Auth::id(),
            'description' => $description,
        ]);

        // Get the project owner and the task assignee
        $projectOwner = $comment->task->project->owner;
        $taskAssignee = $comment->task->assignee;

        // Create a collection of users to notify
        $usersToNotify = collect();

        // Add the project owner if they didn't write the comment
        if ($projectOwner->id !== Auth::id()) {
            $usersToNotify->push($projectOwner);
        }

        // Add the task assignee if they exist and didn't write the comment
        if ($taskAssignee && $taskAssignee->id !== Auth::id()) {
            $usersToNotify->push($taskAssignee);
        }

        // Send the notification to each unique user
        foreach ($usersToNotify->unique('id') as $user) {
            $user->notify(new NewComment($comment));
        }
    }

    /**
     * Handle the Comment "deleted" event.
     */
    public function deleted(Comment $comment): void
    {
        $description = "deleted a comment from the task \"{$comment->task->name}\"";

        $comment->task->project->activities()->create([
            'user_id' => Auth::id(),
            'description' => $description,
        ]);
    }
}