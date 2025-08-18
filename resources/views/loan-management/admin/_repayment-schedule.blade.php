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
                        <th scope="col" class="px-6 py-3">Penalty</th>
                        <th scope="col" class="px-6 py-3"># of Due</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($schedules as $schedule)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4">{{ $schedule->payment_number }}</td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($schedule->due_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4">$ {{ number_format($schedule->payment_amount, 2) }}</td>
                        <td class="px-6 py-4">$ {{ number_format($schedule->principal_component, 2) }}</td>
                        <td class="px-6 py-4">$ {{ number_format($schedule->interest_component, 2) }}</td>
                        <td class="px-6 py-4">$ {{ number_format($schedule->remaining_balance, 2) }}</td>
                        <td class="px-6 py-4">$ {{ number_format($schedule->penalty_amount, 2) }}</td>
                        <td class="px-6 py-4">{{ $schedule->last_penalty_date ? \Carbon\Carbon::parse($schedule->due_date)->diffInDays(\Carbon\Carbon::parse($schedule->last_penalty_date)) : 0 }} days</td>
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
                        <td class="px-6 py-4">
                            @if(!in_array($schedule->status, ['paid', 'paid_late']))
                                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'record-payment-modal-{{ $schedule->id }}')" class="font-medium text-blue-600 dark:text-blue-500 hover:underline text-left">
                                    Record Payment
                                </button>
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

<!-- Modals for each schedule entry -->
@foreach($schedules as $schedule)
<x-modal name="record-payment-modal-{{ $schedule->id }}" focusable>
    <form method="post" action="{{ route('loans.admin.repayment.pay', $schedule) }}" class="p-6">
        @csrf
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Record Payment for Installment #{{ $schedule->payment_number }}</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Total Due: ${{ number_format(bcadd($schedule->payment_amount, $schedule->penalty_amount, 2), 2) }}</p>
        
        <div class="mt-6">
            <x-input-label for="amount_paid_{{ $schedule->id }}" value="Amount Paid" />
            <x-text-input id="amount_paid_{{ $schedule->id }}" name="amount_paid" type="number" class="mt-1 block w-full" step="0.01" required />
        </div>

        <div class="mt-4">
            <x-input-label for="payment_date_{{ $schedule->id }}" value="Payment Date" />
            <x-text-input id="payment_date_{{ $schedule->id }}" name="payment_date" type="date" class="mt-1 block w-full" value="{{ now()->format('Y-m-d') }}" required />
        </div>

        <div class="mt-4">
            <x-input-label for="payment_method_{{ $schedule->id }}" value="Payment Method" />
            <x-text-input id="payment_method_{{ $schedule->id }}" name="payment_method" type="text" class="mt-1 block w-full" value="Cash" required />
        </div>

        <div class="mt-4">
            <x-input-label for="notes_{{ $schedule->id }}" value="Notes (Optional)" />
            <textarea id="notes_{{ $schedule->id }}" name="notes" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"></textarea>
        </div>

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
            <x-primary-button class="ms-3">{{ __('Record Payment') }}</x-primary-button>
        </div>
    </form>
</x-modal>
@endforeach