<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New Loan Payoff') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        Select an active loan to calculate the final payoff amount.
                    </p>
                    <form method="GET" action="{{ route('loans.admin.payoffs.calculate') }}"> 
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Select Active Loan -->
                            <div>
                                <x-input-label for="loan_id" :value="__('Select an Active Loan')" />
                                <select id="loan_id" name="loan_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Please choose a loan --</option>
                                    @foreach ($activeLoans as $loan)
                                        <option value="{{ $loan->id }}">
                                            {{ $loan->loan_identifier }} - {{ $loan->customer->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payoff Date -->
                            <div>
                                <x-input-label for="payoff_date" :value="__('Payoff Date')" />
                                <x-text-input id="payoff_date" class="block mt-1 w-full" type="date" name="payoff_date" value="{{ \Carbon\Carbon::parse($systemDate)->format('Y-m-d') }}" required />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('loans.admin.payoffs.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Calculate Payoff') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>