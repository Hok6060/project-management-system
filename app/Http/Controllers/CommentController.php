<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created comment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Task $task)
    {
        // Add this authorization check
        $this->authorize('create', [Comment::class, $task]);

        // Validate the request
        $request->validate([
            'body' => 'required|string',
        ]);

        // Create the comment
        $task->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        // Redirect back to the project show page
        return redirect()->route('projects.show', $task->project)->with('success', 'Comment added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        $this->authorize('update', $comment);
        return view('comments.edit', compact('comment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $request->validate([
            'body' => 'required|string',
        ]);

        $comment->update($request->only('body'));

        return redirect()->route('tasks.show', $comment->task)->with('success', 'Comment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $task = $comment->task; // Get the task before deleting the comment
        $comment->delete();

        return redirect()->route('tasks.show', $task)->with('success', 'Comment deleted successfully.');
    }
}
