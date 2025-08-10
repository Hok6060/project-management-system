<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Loan Type') }}: {{ $loanType->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.loan-types.update', $loanType) }}">
                        @csrf
                        @method('PATCH')

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Type Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $loanType->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $loanType->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Calculation Type -->
                        <div class="mt-4">
                            <x-input-label for="calculation_type" :value="__('Interest Calculation Type')" />
                            <select id="calculation_type" name="calculation_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="declining_balance" @selected(old('calculation_type', $loanType->calculation_type) == 'declining_balance')>Declining Balance</option>
                                <option value="flat_interest" @selected(old('calculation_type', $loanType->calculation_type) == 'flat_interest')>Flat Interest</option>
                                <option value="interest_only" @selected(old('calculation_type', $loanType->calculation_type) == 'interest_only')>Interest Only</option>
                            </select>
                            <x-input-error :messages="$errors->get('calculation_type')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <!-- Min Interest Rate -->
                            <div>
                                <x-input-label for="min_interest_rate" :value="__('Min Interest Rate (%)')" />
                                <x-text-input id="min_interest_rate" class="block mt-1 w-full" type="number" name="min_interest_rate" :value="old('min_interest_rate', $loanType->min_interest_rate)" required step="0.01" />
                                <x-input-error :messages="$errors->get('min_interest_rate')" class="mt-2" />
                            </div>

                            <!-- Max Interest Rate -->
                            <div>
                                <x-input-label for="max_interest_rate" :value="__('Max Interest Rate (%)')" />
                                <x-text-input id="max_interest_rate" class="block mt-1 w-full" type="number" name="max_interest_rate" :value="old('max_interest_rate', $loanType->max_interest_rate)" required step="0.01" />
                                <x-input-error :messages="$errors->get('max_interest_rate')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <!-- Min Term -->
                            <div>
                                <x-input-label for="min_term" :value="__('Min Term (Months)')" />
                                <x-text-input id="min_term" class="block mt-1 w-full" type="number" name="min_term" :value="old('min_term', $loanType->min_term)" required />
                                <x-input-error :messages="$errors->get('min_term')" class="mt-2" />
                            </div>

                            <!-- Max Term -->
                            <div>
                                <x-input-label for="max_term" :value="__('Max Term (Months)')" />
                                <x-text-input id="max_term" class="block mt-1 w-full" type="number" name="max_term" :value="old('max_term', $loanType->max_term)" required />
                                <x-input-error :messages="$errors->get('max_term')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Is Active -->
                        <div class="block mt-4">
                            <label for="is_active" class="inline-flex items-center">
                                <input id="is_active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="is_active" value="1" @checked(old('is_active', $loanType->is_active))>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Type is Active') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.loan-types.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Update Type') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>