<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Loan Payoff Calculation') }}
            </h2>
            <a href="{{ route('loans.admin.payoffs.create') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Back to Payoffs
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Payoff Details for Loan: {{ $loan->loan_identifier }} as of {{ $payoffDate->format('M d, Y') }}
                    </h3>

                    <!-- Payoff Breakdown -->
                    <div class="space-y-2 border-b dark:border-gray-700 pb-4 mb-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Current Principal Balance:</span>
                            <span class="font-semibold">$ {{ number_format($currentBalance, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Accrued Interest (Since {{ \Carbon\Carbon::parse($interestAccrualFrom)->format('M d, Y') }}):</span>
                            <span class="font-semibold">$ {{ number_format($accruedInterest, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Outstanding Late Penalties:</span>
                            <span class="font-semibold">$ {{ number_format($outstandingPenalties, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Early Payoff Penalty:</span>
                            <span class="font-semibold">$ {{ number_format($earlyPayoffPenalty, 2) }}</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between text-xl font-bold">
                        <span>Total Payoff Amount:</span>
                        <span>$ {{ number_format($totalPayoff, 2) }}</span>
                    </div>

                    <div class="mt-6 border-t dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Confirm Payoff</h3>
                        
                        @if (bccomp($loan->credit_balance, $totalPayoff, 2) >= 0)
                            <p class="text-sm text-gray-600 dark:text-gray-400">The customer has sufficient credit to pay off this loan. This action will use the credit balance to close the loan. This cannot be undone.</p>
                            <form method="POST" action="{{ route('loans.admin.payoffs.store', $loan) }}" class="mt-4">
                                @csrf
                                <input type="hidden" name="payoff_date" value="{{ $payoffDate->format('Y-m-d') }}">
                                <x-danger-button onclick="return confirm('Are you sure you want to use the credit balance to pay off this loan?')">
                                    {{ __('Confirm and Close Loan') }}
                                </x-danger-button>
                            </form>
                        @else
                            <p class="text-sm text-red-600 dark:text-red-400">
                                <strong>Insufficient Credit Balance.</strong> The customer does not have enough available credit to pay off this loan. Please record additional payments first.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>