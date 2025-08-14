<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Loan Activity Log</h3>
        <div class="space-y-4">
            @forelse ($loan->activities as $activity)
                <div class="flex items-start space-x-3">
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
                        @if ($activity->details)
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 border-l-2 border-gray-300 dark:border-gray-600 pl-2 italic">
                                "{{ $activity->details }}"
                            </p>
                        @endif
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $activity->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400">No activity has been logged for this loan yet.</p>
            @endforelse
        </div>
    </div>
</div>