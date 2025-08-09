<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Total Projects Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Total Projects</h3>
                        <p class="mt-1 text-3xl font-semibold">{{ $totalProjects }}</p>
                    </div>
                </div>

                <!-- Total Tasks Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">My Tasks</h3>
                        <p class="mt-1 text-3xl font-semibold">{{ $totalTasks }}</p>
                    </div>
                </div>
            </div>

            <!-- Tasks Due Soon -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Tasks Due Soon (Next 7 Days)</h3>
                    <div class="space-y-4">
                        @forelse ($tasksDueSoon as $task)
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $task->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Project: <a href="{{ route('projects.show', $task->project) }}" class="hover:underline">{{ $task->project->name }}</a>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-red-600 dark:text-red-400">
                                        Due: {{ $task->due_date->format('M d, Y') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        ({{ $task->due_date->diffForHumans() }})
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">You have no tasks due in the next 7 days. Great job!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
