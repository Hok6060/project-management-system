<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-2 sm:space-y-0">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('All Notifications') }}
            </h2>
            <div class="flex items-center space-x-2">
                <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
                    @csrf
                    <x-primary-button>
                    {{ __('Mark All as Read') }}
                    </x-primary-button>
                </form>
                <form action="{{ route('notifications.clearRead') }}" method="POST">
                    @csrf
                    <x-secondary-button type="submit" onclick="return confirm('Are you sure you want to clear all read notifications?')">
                    {{ __('Clear All Read') }}
                    </x-secondary-button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="space-y-4">
                        @forelse ($notifications as $notification)
                            <div class="p-4 rounded-lg flex items-center justify-between {{ $notification->read_at ? 'bg-gray-100 dark:bg-gray-700' : 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800' }}">
                                <div class="flex-grow">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $notification->data['message'] }}</p>
                                    @if (isset($notification->data['task_name']))
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ \Illuminate\Support\Str::limit($notification->data['task_name'], 40) }} in {{ $notification->data['project_name'] }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-4 ml-4">
                                    @if (!$notification->read_at)
                                        <a href="{{ route('notifications.read', $notification) }}" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap">Mark as Read & View</a>
                                    @else
                                         <a href="{{ $notification->data['url'] }}" class="text-sm font-medium text-gray-500 dark:text-gray-400 hover:underline whitespace-nowrap">View Task</a>
                                    @endif
                                    <!-- Delete Button Form -->
                                    <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 dark:text-red-500 hover:underline" onclick="return confirm('Are you sure?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">You have no notifications.</p>
                        @endforelse
                    </div>

                    <!-- Pagination Links -->
                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
