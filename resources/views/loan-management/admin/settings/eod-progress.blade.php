<x-app-layout>
    <!-- Add a meta refresh tag to the header to reload the page every 3 seconds -->
    <x-slot name="header">
        <meta http-equiv="refresh" content="3">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('End-of-Day Process is Running...') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        This page will automatically refresh every 3 seconds. The process is running in the background. You can safely navigate away from this page.
                    </p>
                    
                    <h4 class="font-semibold">Live Log Output:</h4>
                    <pre class="mt-2 p-4 bg-gray-900 text-black text-xs rounded-md whitespace-pre-wrap h-96 overflow-y-auto">{{ $logContent }}</pre>

                    <div class="mt-6">
                        <a href="{{ route('admin.settings.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                            &larr; Back to Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>