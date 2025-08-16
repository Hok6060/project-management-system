<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('System Settings') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Back to Admin
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Settings Form -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- System Date -->
                            <div>
                                <x-input-label for="system_date" :value="__('Current System Date')" />
                                <x-text-input id="system_date" class="block mt-1 w-full" type="date" name="system_date" :value="old('system_date', \Carbon\Carbon::parse($settings->system_date)->format('Y-m-d'))" required />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">All automated tasks like penalties will run based on this date.</p>
                                <x-input-error :messages="$errors->get('system_date')" class="mt-2" />
                            </div>

                            <!-- EOD Mode -->
                            <div>
                                <x-input-label for="eod_mode" :value="__('End-of-Day (EOD) Mode')" />
                                <select id="eod_mode" name="eod_mode" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="auto" @selected(old('eod_mode', $settings->eod_mode) == 'auto')>Automatic</option>
                                    <option value="manual" @selected(old('eod_mode', $settings->eod_mode) == 'manual')>Manual</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">'Automatic' runs every night. 'Manual' requires you to trigger it.</p>
                                <x-input-error :messages="$errors->get('eod_mode')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Save Settings') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- EOD Actions -->
            <div class="mt-4 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Manual EOD Actions</h3>
                    <div class="flex items-center space-x-4">
                        <form method="POST" action="{{ route('admin.settings.runEod') }}">
                            @csrf
                            <x-danger-button type="submit" onclick="return confirm('Are you sure you want to run the EOD process and advance the system date by one day?')">
                                Run EOD
                            </x-danger-button>
                        </form>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">This will apply penalties for the current system date and then move the date to the next day.</p>

                    @if(session('eod_output'))
                        <div class="mt-4">
                            <h4 class="font-semibold">EOD Process Output:</h4>
                            <pre class="mt-2 p-4 bg-gray-100 dark:bg-gray-900 rounded-md text-xs whitespace-pre-wrap">{{ session('eod_output') }}</pre>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>