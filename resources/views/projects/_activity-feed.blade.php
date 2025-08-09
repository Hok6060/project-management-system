<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Project Activity</h3>
        <div class="space-y-4">
            @forelse ($project->activities as $activity)
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <!-- You can add user avatars here later -->
                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600"></div>
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
    </div>
</div>
