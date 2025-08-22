<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('End-of-Day Process Status') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div
                x-data="eodProgress({{ $jobStatus->id }})"
                x-init="startPolling()"
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
            >
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Overall Status -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center mb-1">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" x-text="title"></h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400" x-text="status"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700">
                            <div class="bg-blue-600 text-xs font-medium text-blue-100 text-center p-0.5 leading-none rounded-full" :style="`width: ${progress}%`" x-text="`${progress}%`"></div>
                        </div>
                    </div>

                    <!-- Log Output -->
                    <div>
                        <h4 class="font-semibold mb-2">Live Log:</h4>
                        <pre x-text="formattedLogs" class="p-4 bg-gray-900 text-white text-xs rounded-md whitespace-pre-wrap h-96 overflow-y-auto"></pre>
                    </div>

                    <!-- Final Message -->
                    <div x-show="isFinished" class="mt-6" style="display: none;">
                        <div
                            :class="{
                                'bg-green-100 dark:bg-green-900 border-green-500 text-green-800 dark:text-green-200': finalStatus === 'completed',
                                'bg-red-100 dark:bg-red-900 border-red-500 text-red-800 dark:text-red-200': finalStatus === 'failed'
                            }"
                            class="border-l-4 p-4"
                            role="alert"
                        >
                            <p class="font-bold" x-text="finalStatus === 'completed' ? 'Success' : 'Failed'"></p>
                            <p x-text="finalOutput"></p>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.settings.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                &larr; Back to Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('eodProgress', (jobStatusId) => ({
                jobId: jobStatusId,
                title: 'EOD Process Initializing...',
                status: 'Queued',
                progress: 0,
                logs: [],
                isFinished: false,
                finalStatus: '',
                finalOutput: '',
                interval: null,

                startPolling() {
                    console.log('Starting to poll for Job ID:', this.jobId);
                    if (!this.jobId) {
                        this.title = 'Error: Job ID not found.';
                        return;
                    }
                    this.fetchStatus();
                    this.interval = setInterval(() => {
                        this.fetchStatus();
                    }, 2000);
                },

                fetchStatus() {
                    fetch(`/admin/eod-status/${this.jobId}`)
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Received data:', data);
                            this.title = `EOD Process - ${data.status.charAt(0).toUpperCase() + data.status.slice(1)}`;
                            this.status = data.status;
                            this.progress = data.progress;
                            this.logs = data.details && data.details.logs ? data.details.logs : ['Waiting for logs...'];

                            if (data.status === 'completed' || data.status === 'failed') {
                                console.log('Job finished with status:', data.status);
                                this.isFinished = true;
                                this.finalStatus = data.status;
                                this.finalOutput = data.output;
                                clearInterval(this.interval);
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error);
                            this.title = 'Error: Could not fetch job status.';
                            clearInterval(this.interval);
                        });
                },

                get formattedLogs() {
                    return this.logs.join('\n');
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>