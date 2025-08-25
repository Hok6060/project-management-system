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

                    <!-- We will add the confirmation form here in the next step -->
                    <div class="mt-6 border-t dark:border-gray-700 pt-6">
                         <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Confirm Payoff Payment</h3>
                         <p class="text-sm text-gray-500 dark:text-gray-400">This action will record the final payment, close all remaining installments, and mark the loan as 'Completed'. This cannot be undone.</p>
                         <div class="mt-4">
                            <x-danger-button>
                                {{ __('Confirm and Record Payoff') }}
                            </x-danger-button>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>