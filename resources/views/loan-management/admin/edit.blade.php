<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Loan Application') }}: {{ $loan->loan_identifier }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('loans.admin.update', $loan) }}">
                        @csrf
                        @method('PATCH')

                        <!-- Customer -->
                        <div>
                            <x-input-label for="customer_id" :value="__('Select Customer')" />
                            <select id="customer_id" name="customer_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id', $loan->customer_id) == $customer->id)>
                                        {{ $customer->full_name }} ({{ $customer->customer_identifier }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
                        </div>

                        <!-- Loan Type -->
                        <div class="mt-4">
                            <x-input-label for="loan_type_id" :value="__('Select Loan Type')" />
                            <select id="loan_type_id" name="loan_type_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                @foreach ($loanTypes as $type)
                                    <option 
                                        value="{{ $type->id }}" 
                                        @selected(old('loan_type_id', $loan->loan_type_id) == $type->id)
                                        data-min-rate="{{ $type->min_interest_rate }}"
                                        data-max-rate="{{ $type->max_interest_rate }}"
                                        data-min-term="{{ $type->min_term }}"
                                        data-max-term="{{ $type->max_term }}"
                                    >
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('loan_type_id')" class="mt-2" />
                        </div>

                        <!-- Loan Officer -->
                        <div class="mt-4">
                            <x-input-label for="loan_officer_id" :value="__('Assign to Loan Officer')" />
                            <select id="loan_officer_id" name="loan_officer_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">-- Unassigned --</option>
                                @foreach ($loanOfficers as $officer)
                                    <option value="{{ $officer->id }}" @selected(old('loan_officer_id', $loan->loan_officer_id) == $officer->id)>
                                        {{ $officer->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('loan_officer_id')" class="mt-2" />
                        </div>

                        <!-- Principal Amount -->
                        <div class="mt-4">
                            <x-input-label for="principal_amount" :value="__('Loan Amount ($)')" />
                            <x-text-input id="principal_amount" class="block mt-1 w-full" type="number" name="principal_amount" :value="old('principal_amount', $loan->principal_amount)" required step="100" />
                            <x-input-error :messages="$errors->get('principal_amount')" class="mt-2" />
                        </div>

                        <!-- Interest Rate -->
                        <div class="mt-4">
                            <x-input-label for="interest_rate" :value="__('Interest Rate (%)')" />
                            <x-text-input id="interest_rate" class="block mt-1 w-full" type="number" name="interest_rate" :value="old('interest_rate', $loan->interest_rate)" required step="0.01" />
                            <p id="rate-helper" class="mt-1 text-xs text-gray-500 dark:text-gray-400"></p>
                            <x-input-error :messages="$errors->get('interest_rate')" class="mt-2" />
                        </div>

                        <!-- Term -->
                        <div class="mt-4">
                            <x-input-label for="term" :value="__('Loan Term (in Months)')" />
                            <x-text-input id="term" class="block mt-1 w-full" type="number" name="term" :value="old('term', $loan->term)" required />
                            <p id="term-helper" class="mt-1 text-xs text-gray-500 dark:text-gray-400"></p>
                            <x-input-error :messages="$errors->get('term')" class="mt-2" />
                        </div>

                        <!-- Interest-Free Periods -->
                        <div class="mt-4">
                            <x-input-label for="interest_free_periods" :value="__('Promotional Interest-Free Periods (Months)')" />
                            <x-text-input id="interest_free_periods" class="block mt-1 w-full" type="number" name="interest_free_periods" :value="old('interest_free_periods', $loan->interest_free_periods)" />
                            <x-input-error :messages="$errors->get('interest_free_periods')" class="mt-2" />
                        </div>

                        <!-- Payment Frequency -->
                        <div class="mt-4">
                            <x-input-label for="payment_frequency" :value="__('Payment Frequency')" />
                            <select id="payment_frequency" name="payment_frequency" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="monthly" @selected(old('payment_frequency', $loan->payment_frequency) == 'monthly')>Monthly</option>
                                <option value="quarterly" @selected(old('payment_frequency', $loan->payment_frequency) == 'quarterly')>Quarterly</option>
                                <option value="semi_annually" @selected(old('payment_frequency', $loan->payment_frequency) == 'semi_annually')>Semi-Annually</option>
                            </select>
                            <x-input-error :messages="$errors->get('payment_frequency')" class="mt-2" />
                        </div>

                        <!-- First Payment Date -->
                        <div class="mt-4">
                            <x-input-label for="first_payment_date" :value="__('First Payment Due Date')" />
                            <x-text-input id="first_payment_date" class="block mt-1 w-full" type="date" name="first_payment_date" :value="old('first_payment_date', \Carbon\Carbon::parse($loan->first_payment_date)->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('first_payment_date')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('loans.admin.show', $loan) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Update Application') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const productSelect = document.getElementById('loan_type_id');
            const rateHelper = document.getElementById('rate-helper');
            const termHelper = document.getElementById('term-helper');

            function updateHelpers() {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const minRate = selectedOption.dataset.minRate;
                const maxRate = selectedOption.dataset.maxRate;
                const minTerm = selectedOption.dataset.minTerm;
                const maxTerm = selectedOption.dataset.maxTerm;

                if (minRate) {
                    rateHelper.textContent = `Must be between ${minRate}% and ${maxRate}%.`;
                    termHelper.textContent = `Must be between ${minTerm} and ${maxTerm} months.`;
                } else {
                    rateHelper.textContent = '';
                    termHelper.textContent = '';
                }
            }

            productSelect.addEventListener('change', updateHelpers);
            updateHelpers();
        });
    </script>
    @endpush
</x-app-layout>