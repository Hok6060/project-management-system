<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Paid Off Loans') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('loans.admin.payoffs.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            {{ __('New Payoff') }}
                        </a>
                    </div>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Loan ID</th>
                                    <th scope="col" class="px-6 py-3">Customer</th>
                                    <th scope="col" class="px-6 py-3">Loan Type</th>
                                    <th scope="col" class="px-6 py-3">Amount</th>
                                    <th scope="col" class="px-6 py-3">Completed On</th>
                                    <th scope="col" class="px-6 py-3"><span class="sr-only">View</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($paidOffLoans as $loan)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $loan->loan_identifier }}
                                    </th>
                                    <td class="px-6 py-4">{{ $loan->customer->full_name }}</td>
                                    <td class="px-6 py-4">{{ $loan->loanType->name }}</td>
                                    <td class="px-6 py-4">${{ number_format($loan->principal_amount, 2) }}</td>
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($loan->updated_at)->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('loans.admin.show', $loan) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No loans have been paid off yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $paidOffLoans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>