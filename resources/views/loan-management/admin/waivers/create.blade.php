<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Request New Waiver for Loan') }}: {{ $loan->loan_identifier }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100"
                    x-data="{
                        waiverType: '',
                        maxAmount: 0,
                        isLoading: false,
                        fetchMaxAmount() {
                            if (!this.waiverType) {
                                this.maxAmount = 0;
                                return;
                            }
                            this.isLoading = true;
                            fetch(`{{ route('loans.admin.waivers.calculateMax', $loan) }}?type=${this.waiverType}`)
                                .then(res => res.json())
                                .then(data => {
                                    this.maxAmount = data.max_waiver_amount;
                                    this.isLoading = false;
                                });
                        }
                    }"
                >
                    <form method="POST" action="{{ route('loans.admin.waivers.store', $loan) }}">
                        @csrf

                        <!-- Waiver Type -->
                        <div>
                            <x-input-label for="waiver_type" :value="__('Waiver Type')" />
                            <select x-model="waiverType" @change="fetchMaxAmount()" id="waiver_type" name="waiver_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">-- Select a type --</option>
                                <option value="late_penalty">Late Penalty</option>
                                <option value="interest">Interest</option>
                                <option value="principal">Principal</option>
                            </select>
                        </div>

                        <!-- Amount -->
                        <div class="mt-4">
                            <x-input-label for="amount" :value="__('Amount to Waive ($)')" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" :value="old('amount')" required step="0.01" ::max="maxAmount" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                <span x-show="isLoading">Calculating...</span>
                                <span x-show="!isLoading && waiverType">Max waivable amount: <strong x-text="`$${maxAmount}`"></strong></span>
                            </p>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <!-- Reason -->
                        <div class="mt-4">
                            <x-input-label for="reason" :value="__('Reason for Waiver Request')" />
                            <textarea id="reason" name="reason" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('loans.admin.show', $loan) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Submit for Approval') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>