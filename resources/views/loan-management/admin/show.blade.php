<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Loan Application') }}: {{ $loan->loan_identifier }}
            </h2>
            <div class="flex items-center space-x-4">
                @if ($loan->status === 'approved' || $loan->status === 'active')
                    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'record-customer-payment-modal')">
                        Record Payment
                    </x-primary-button>
                    <a href="{{ route('loans.admin.waivers.create', $loan) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-700 text-white font-bold rounded-md text-sm">
                        Request Waiver
                    </a>
                @endif
                @if ($loan->status === 'pending')
                    <a href="{{ route('loans.admin.edit', $loan) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Edit Application
                    </a>
                @endif
                <a href="{{ route('loans.admin.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                    &larr; Back to All Applications
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Loan Details Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Loan Details</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <h4 class="font-semibold">Application Date</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($loan->application_date)->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Status</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ ucwords($loan->status) }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Loan Type</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $loan->LoanType->name }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Principal Amount</h4>
                            <p class="text-gray-600 dark:text-gray-400">${{ number_format($loan->principal_amount, 2) }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Term</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $loan->term }} months</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Payment Frequency</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ ucwords(str_replace('_', ' ', $loan->payment_frequency)) }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Interest Rate</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $loan->interest_rate }}%</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Free Interest Period</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $loan->interest_free_periods ? $loan->interest_free_periods . " months" : 'N/A' }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">First Payment Date</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($loan->first_payment_date)->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Last Payment Date</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($loan->first_payment_date)->addMonths($loan->term - 1)->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Credit Balance Amount</h4>
                            <p class="text-gray-600 dark:text-gray-400">$ {{ number_format($loan->credit_balance, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Customer Information</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <h4 class="font-semibold">Name</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $loan->customer->full_name }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Phone</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $loan->customer->phone_number }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Address</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $loan->customer->address }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Email</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $loan->customer->email }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">Member Since</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $loan->customer->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assign Loan Officer Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Loan Officer</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Currently Assigned to: <strong>{{ $loan->loanOfficer->name ?? 'Unassigned' }}</strong></p>
                </div>
            </div>

            <!-- Loan Actions Card -->
            @if ($loan->status === 'pending')
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Loan Actions</h3>
                    <div class="flex items-center space-x-4">
                        <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'approve-loan-modal')">{{ __('Approve') }}</x-primary-button>
                        <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'reject-loan-modal')">{{ __('Reject') }}</x-danger-button>
                        <x-secondary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'cancel-loan-modal')">{{ __('Cancel') }}</x-secondary-button>
                    </div>
                </div>
            </div>
            @endif

            <!-- Activity Log -->
            @include('loan-management.admin._activity-log', ['loan' => $loan])

            <!-- Repayment Schedule -->
            @include('loan-management.admin._repayment-schedule', ['loan' => $loan, 'schedules' => $schedules])
            
            <!-- Transaction History Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Transaction History</h3>
                    <div class="space-y-4">
                        @forelse ($loan->transactions as $transaction)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center justify-between">
                                    @php
                                        $method = strtolower($transaction->payment_method);
                                        $isGreen = in_array($method, ['cash', 'bank_transfer']);
                                        $colorClass = $isGreen ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400';
                                    @endphp
                                    <p class="font-semibold {{ $colorClass }}">
                                        ${{ number_format($transaction->amount_paid, 2) }} paid via {{ ucwords(str_replace('_', ' ', $transaction->payment_method)) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($transaction->payment_date)->format('M d, Y') }}
                                    </p>
                                </div>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 border-l-2 border-gray-300 dark:border-gray-600 pl-2 italic">
                                    "{{ $transaction->notes ?? 'N/A' }}"
                                </p>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">No transactions have been recorded for this loan yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Waiver Requests -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Waiver Requests</h3>
                    <div class="space-y-4">
                        @forelse ($loan->waivers as $waiver)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        ${{ number_format($waiver->amount, 2) }} {{ ucwords(str_replace('_', ' ', $waiver->waiver_type)) }} Waiver
                                    </p>
                                    @if($waiver->status == 'approved')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ ucwords($waiver->status) }}
                                        </span>
                                    @elseif($waiver->status == 'pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ ucwords($waiver->status) }}
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ ucwords($waiver->status) }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Requested by {{ $waiver->requester->name }} on {{ $waiver->created_at->format('M d, Y') }}
                                </p>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 border-l-2 border-gray-300 dark:border-gray-600 pl-2 italic">
                                    "{{ $waiver->reason }}"
                                </p>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">No waiver requests have been made for this loan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <x-modal name="approve-loan-modal" focusable>
        <form method="post" action="{{ route('loans.admin.update', $loan) }}" class="p-6">
            @csrf
            @method('patch')
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Approve Loan Application?</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Please provide a comment for this action.</p>
            <div class="mt-6">
                <x-input-label for="details_approve" value="Comment" class="sr-only" />
                <textarea id="details_approve" name="details" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">Approved</textarea>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button class="ms-3" name="status" value="approved">{{ __('Approve Loan') }}</x-primary-button>
            </div>
        </form>
    </x-modal>

    <!-- Reject Modal -->
    <x-modal name="reject-loan-modal" focusable>
        <form method="post" action="{{ route('loans.admin.update', $loan) }}" class="p-6">
            @csrf
            @method('patch')
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Reject Loan Application?</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Please provide a reason for rejecting this application.</p>
            <div class="mt-6">
                <x-input-label for="details_reject" value="Reason" class="sr-only" />
                <textarea id="details_reject" name="details" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Reason for rejection..."></textarea>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button class="ms-3" name="status" value="rejected">{{ __('Reject Loan') }}</x-danger-button>
            </div>
        </form>
    </x-modal>

    <!-- Cancel Modal -->
    <x-modal name="cancel-loan-modal" focusable>
        <form method="post" action="{{ route('loans.admin.cancel', $loan) }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Cancel Loan Application?</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Please provide a reason for cancelling this application.</p>
            <div class="mt-6">
                <x-input-label for="details_cancel" value="Reason" class="sr-only" />
                <textarea id="details_cancel" name="details" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" placeholder="Reason for cancellation..." required></textarea>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-secondary-button class="ms-3" type="submit">{{ __('Confirm Cancellation') }}</x-secondary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>

<!-- Record Customer Payment Modal -->
<x-modal name="record-customer-payment-modal" focusable>
    <form method="post" action="{{ route('loans.admin.payment.store', $loan) }}" class="p-6">
        @csrf
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Record Payment for {{ $loan->customer->full_name }}</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Record a new payment for loan {{ $loan->loan_identifier }}. The system will automatically allocate the funds to the oldest outstanding installments.</p>

        <div class="mt-6">
            <x-input-label for="amount_paid" value="Amount Paid" />
            <x-text-input id="amount_paid" name="amount_paid" type="number" class="mt-1 block w-full" step="0.01" required />
        </div>

        <div class="mt-4">
            <x-input-label for="payment_date" value="Payment Date" />
            <x-text-input id="payment_date" name="payment_date" type="date" class="mt-1 block w-full" value="{{ \Carbon\Carbon::parse($systemDate)->format('Y-m-d') }}" required />
        </div>

        <div class="mt-4">
            <x-input-label for="payment_method" value="Payment Method" />
            <select id="payment_method" name="payment_method" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cash">Cash</option>
            </select>
        </div>

        <div class="mt-4">
            <x-input-label for="notes" value="Notes (Optional)" />
            <textarea id="notes" name="notes" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"></textarea>
        </div>

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
            <x-primary-button class="ms-3">{{ __('Record Payment') }}</x-primary-button>
        </div>
    </form>
</x-modal>