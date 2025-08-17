<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('All Loan Applications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="GET" action="{{ route('loans.admin.index') }}" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="q" :value="__('Search')" />
                                <x-text-input id="q" class="block mt-1 w-full" type="text" name="q" :value="$request->q" placeholder="Customer name or Loan ID..." />
                            </div>
                            <div>
                                <x-input-label for="status" :value="__('Filter by Status')" />
                                <select id="status" name="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">All Statuses</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" @selected($request->status == $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end space-x-4">
                                <x-primary-button class="h-10">
                                    {{ __('Filter') }}
                                </x-primary-button>
                                <x-secondary-button class="h-10" @click="resetFilters">
                                    {{ __('Reset') }}
                                </x-secondary-button>
                            </div>
                        </div>
                    </form>
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Loan ID</th>
                                    <th scope="col" class="px-6 py-3">Customer</th>
                                    <th scope="col" class="px-6 py-3">Type</th>
                                    <th scope="col" class="px-6 py-3">Amount</th>
                                    <th scope="col" class="px-6 py-3">Credit Officer</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3">Applied On</th>
                                    <th scope="col" class="px-6 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($loans as $loan)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $loan->loan_identifier }}
                                    </th>
                                    <td class="px-6 py-4">{{ $loan->customer->full_name }}</td>
                                    <td class="px-6 py-4">{{ $loan->loanType->name }}</td>
                                    <td class="px-6 py-4">${{ number_format($loan->principal_amount, 2) }}</td>
                                    <td class="px-6 py-4">{{ $loan->loanOfficer->name ?? 'Unassigned' }}</td>
                                    <td class="px-6 py-4">
                                        @if($loan->status == 'active' || $loan->status == 'completed')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        @elseif($loan->status == 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($loan->application_date)->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-left">
                                        <a href="{{ route('loans.admin.show', $loan) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No loan applications found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $loans->appends($request->all())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function resetFilters() {
                const url = new URL(window.location);
                url.searchParams.delete('q');
                url.searchParams.delete('status');
                window.location = url;
            }
        </script>
    @endpush
</x-app-layout>