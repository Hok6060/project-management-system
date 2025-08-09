<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $project->name }}
            </h2>
            <div class="flex items-center space-x-4">
                <!-- View Activity Button -->
                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'activity-modal')" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-700 text-white font-bold rounded-md text-sm">
                    View Activity
                </button>

                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Edit Project
                    </a>
                @endcan
                @can('delete', $project)
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-800 text-white font-bold py-2 px-4 rounded text-sm" onclick="return confirm('Are you sure you want to delete this project and all its tasks?')">
                            Delete Project
                        </button>
                    </form>
                @endcan
                <a href="{{ route('projects.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                    &larr; Back to Projects
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Project Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Project Details</h3>
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold">Description</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $project->description ?? 'No description provided.' }}</p>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <h4 class="font-semibold">Status</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</p>
                            </div>
                             <div>
                                <h4 class="font-semibold">Start Date</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ $project->start_date->format('M d, Y') }}</p>
                            </div>
                            <div>
                                <h4 class="font-semibold">End Date</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ $project->end_date ? $project->end_date->format('M d, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <h4 class="font-semibold">Budget</h4>
                                <p class="text-gray-600 dark:text-gray-400">${{ number_format($project->budget, 2) }}</p>
                            </div>
                            <div>
                                <h4 class="font-semibold">Owner</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ $project->owner->name }}</p>
                            </div>
                             <div>
                                <h4 class="font-semibold">Client</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ $project->client->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tasks</h3>
                        @can('create', [App\Models\Task::class, $project])
                            <a href="{{ route('tasks.create', $project) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Add Task
                            </a>
                        @endcan
                    </div>
                    @include('projects._tasks-table', ['tasks' => $project->tasks])
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Modal -->
    <x-modal name="activity-modal" :show="$errors->isNotEmpty()" focusable>
        <div class="p-6 bg-white dark:bg-gray-800">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Project Activity') }}
            </h2>

            <div class="mt-6 space-y-4 max-h-96 overflow-y-auto">
                @forelse ($project->activities as $activity)
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs text-gray-500">
                                {{ substr($activity->user->name, 0, 1) }}
                            </div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-semibold">{{ $activity->user->name }}</span>
                                {{ $activity->description }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $activity->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">No activity yet for this project.</p>
                @endforelse
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Close') }}
                </x-secondary-button>
            </div>
        </div>
    </x-modal>
</x-app-layout>
