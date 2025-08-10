<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Repayment Schedule</h3>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">#</th>
                        <th scope="col" class="px-6 py-3">Due Date</th>
                        <th scope="col" class="px-6 py-3">Payment Amount</th>
                        <th scope="col" class="px-6 py-3">Principal</th>
                        <th scope="col" class="px-6 py-3">Interest</th>
                        <th scope="col" class="px-6 py-3">Remaining Balance</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4">{{ $schedule->payment_number }}</td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($schedule->due_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4">${{ number_format($schedule->payment_amount, 2) }}</td>
                        <td class="px-6 py-4">${{ number_format($schedule->principal_component, 2) }}</td>
                        <td class="px-6 py-4">${{ number_format($schedule->interest_component, 2) }}</td>
                        <td class="px-6 py-4">${{ number_format($schedule->remaining_balance, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($schedule->status == 'paid')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ ucfirst($schedule->status) }}
                                </span>
                            @elseif($schedule->status == 'pending')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ ucfirst($schedule->status) }}
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ ucfirst($schedule->status) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            The repayment schedule will be generated once the loan is approved.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $schedules->links() }}
        </div>
    </div>
    
</div>