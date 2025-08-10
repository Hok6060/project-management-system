<?php

namespace App\ProjectManagement\Http\Controllers;

use App\ProjectManagement\Models\Task;
use App\ProjectManagement\Models\Attachment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; 

class AttachmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created attachment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ProjectManagement\Models\Task  $task
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Task $task)
    {
        $this->authorize('create', [Attachment::class, $task]);

        // Validate the uploaded file
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,bmp,gif,svg,pdf,doc,docx,xls,xlsx,txt|max:2048', // Max 2MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Store the file in 'storage/app/public/attachments'
            // The store method returns the path of the stored file
            $filePath = $file->store('attachments', 'public');

            // Create the attachment record in the database
            $task->attachments()->create([
                'user_id' => Auth::id(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'File attached successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attachment $attachment)
    {
        $this->authorize('delete', $attachment);

        // Delete the file from storage
        Storage::disk('public')->delete($attachment->file_path);

        // Delete the record from the database
        $attachment->delete();

        return redirect()->back()->with('success', 'Attachment deleted successfully.');
    }
}
