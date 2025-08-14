<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Loan Application') }}: {{ $loan->loan_identifier }}
            </h2>
            <div class="flex items-center space-x-4">
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
                            <p class="text-gray-600 dark:text-gray-400">{{ ucfirst($loan->status) }}</p>
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
                            <p class="text-gray-600 dark:text-gray-400">{{ $loan->interest_free_periods }} months</p>
                        </div>
                        <div>
                            <h4 class="font-semibold">First Payment Date</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ \Carbon\Carbon::parse($loan->first_payment_date)->format('M d, Y') }}</p>
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