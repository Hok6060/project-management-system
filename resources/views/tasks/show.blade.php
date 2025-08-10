<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Task: <span class="italic">{{ $task->name }}</span>
            </h2>
            <a href="{{ route('projects.show', $task->project) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Back to Project
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Task Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Task Details</h3>
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold">Description</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $task->description ?? 'No description provided.' }}</p>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <h4 class="font-semibold">Status</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</p>
                            </div>
                             <div>
                                <h4 class="font-semibold">Priority</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ ucfirst($task->priority) }}</p>
                            </div>
                            <div>
                                <h4 class="font-semibold">Due Date</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <h4 class="font-semibold">Assignee</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ $task->assignee->name ?? 'Unassigned' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Comments</h3>
                    
                    <!-- New Comment Form -->
                    @can('create', [App\ProjectManagement\Models\Comment::class, $task])
                        <form action="{{ route('comments.store', $task) }}" method="POST" class="mb-6">
                            @csrf
                            <div>
                                <x-input-label for="body" class="sr-only">Comment</x-input-label>
                                <textarea id="body" name="body" rows="3" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Add a comment..."></textarea>
                                <x-input-error :messages="$errors->get('body')" class="mt-2" />
                            </div>
                            <div class="mt-2">
                                <x-primary-button>Post Comment</x-primary-button>
                            </div>
                        </form>
                    @endcan

                    <!-- Existing Comments -->
                    <div class="space-y-4">
                        @forelse ($task->comments as $comment)
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $comment->user->name }}</p>
                                    <span class="text-gray-500 dark:text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-center">
                                    <div class="md:col-span-5">
                                        <p class="text-gray-700 dark:text-gray-300">{{ $comment->body }}</p>
                                    </div>
                                    <div class="flex items-center justify-end space-x-4 md:col-span-1">
                                        @can('update', $comment)
                                            <a href="{{ route('comments.edit', $comment) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                        @endcan
                                        @can('delete', $comment)
                                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">No comments yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Attachments Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Attachments</h3>

                    <!-- New Attachment Form -->
                    @can('create', [App\ProjectManagement\Models\Attachment::class, $task])
                        <form action="{{ route('attachments.store', $task) }}" method="POST" enctype="multipart/form-data" class="mb-6 border-b border-gray-200 dark:border-gray-700 pb-6">
                            @csrf
                            <div>
                                <x-input-label for="file" :value="__('Upload New File')" />
                                <x-text-input id="file" name="file" type="file" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('file')" class="mt-2" />
                            </div>
                            <div class="mt-4">
                                <x-primary-button>Attach File</x-primary-button>
                            </div>
                        </form>
                    @endcan

                    <!-- Existing Attachments -->
                    <div class="space-y-4 mt-6">
                        @forelse ($task->attachments as $attachment)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <!-- You can add file type icons here later -->
                                    <div>
                                        <a href="{{ Storage::url($attachment->file_path) }}" target="_blank" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                            {{ $attachment->file_name }}
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Uploaded by {{ $attachment->user->name }} &middot; {{ $attachment->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                                <!-- Delete Attachment Form -->
                                @can('delete', $attachment)
                                <form action="{{ route('attachments.destroy', $attachment) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 dark:text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this file?')">
                                        Delete
                                    </button>
                                </form>
                                @endcan
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">No files have been attached to this task.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
