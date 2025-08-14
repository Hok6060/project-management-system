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

            <!-- Loan Actions Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Loan Actions</h3>

                    @if ($loan->status === 'pending')
                        <form method="POST" action="{{ route('loans.admin.update', $loan) }}">
                            @csrf
                            @method('PATCH')

                            <div class="flex items-center space-x-4">
                                <!-- Approve Button -->
                                <x-primary-button name="status" value="approved" onclick="return confirm('Are you sure you want to approve this loan application?')">
                                    {{ __('Approve') }}
                                </x-primary-button>

                                <!-- Reject Button -->
                                <x-danger-button name="status" value="rejected" onclick="return confirm('Are you sure you want to reject this loan application?')">
                                    {{ __('Reject') }}
                                </x-danger-button>
                            </div>
                        </form>
                    @else
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            This loan application has already been processed. The status is: <strong>{{ ucfirst($loan->status) }}</strong>.
                        </p>
                    @endif

                    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        @if ($loan->loan_officer_id)
                            Assigned to: <strong>{{ $loan->loanOfficer->name }}</strong>
                        @else
                            This loan is unassigned. Approving or rejecting will assign it to you.
                        @endif
                    </div>
                </div>
            </div>

            @include('loan-management.admin._repayment-schedule', ['schedules' => $schedules])
            
        </div>
    </div>
</x-app-layout>