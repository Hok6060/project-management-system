<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Total Users</h3>
                        <p class="mt-1 text-3xl font-semibold">{{ $stats['total_users'] }}</p>
                    </div>
                </div>

                <!-- Total Projects -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Total Projects</h3>
                        <p class="mt-1 text-3xl font-semibold">{{ $stats['total_projects'] }}</p>
                    </div>
                </div>

                <!-- Active Projects -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Active Projects</h3>
                        <p class="mt-1 text-3xl font-semibold">{{ $stats['active_projects'] }}</p>
                    </div>
                </div>

                <!-- Completed Projects -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Completed Projects</h3>
                        <p class="mt-1 text-3xl font-semibold">{{ $stats['completed_projects'] }}</p>
                    </div>
                </div>
            </div>

            <!-- New Chart Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Projects by Status</h3>
                        <div class="h-80">
                            <canvas id="projectStatusChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mt-6 mb-4 px-6">Menu</h3>
                    <div class="px-6 mb-6">
                        <a href="{{ route('admin.users.index') }}" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            Go to User Management &rarr;
                        </a>
                        <a href="{{ route('admin.loan-types.index') }}" class="block font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            Go to Loan Type Management &rarr;
                        </a>
                        <a href="{{ route('admin.customers.index') }}" class="block font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            Go to Customer Management &rarr;
                        </a>
                        <a href="{{ route('admin.waivers.index') }}" class="block font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            Go to Waiver Approvals &rarr;
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="block font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                            Go to System Settings &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script type="module">
        import { Chart, registerables } from 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.js/+esm';
        Chart.register(...registerables);

        document.addEventListener('DOMContentLoaded', function () {
            fetch('{{ route('admin.chart.project.status') }}')
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('projectStatusChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Projects by Status',
                                data: data.data,
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.7)', // Blue
                                    'rgba(75, 192, 192, 0.7)', // Green
                                    'rgba(255, 206, 86, 0.7)', // Yellow
                                    'rgba(255, 99, 132, 0.7)',  // Red
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(255, 99, 132, 1)',
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                title: {
                                    display: false,
                                    text: 'Projects by Status'
                                }
                            }
                        }
                    });
                });
        });
    </script>
    @endpush
</x-app-layout>