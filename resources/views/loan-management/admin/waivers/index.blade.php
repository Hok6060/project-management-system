<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Pending Waiver Approvals') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                &larr; Back to Admin
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="space-y-4">
                        @forelse ($pendingWaivers as $waiver)
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            <a href="{{ route('loans.admin.show', $waiver->loan) }}" class="hover:underline">
                                                {{ $waiver->loan->loan_identifier }}
                                            </a>
                                            - ${{ number_format($waiver->amount, 2) }} {{ ucfirst(str_replace('_', ' ', $waiver->waiver_type)) }}
                                        </p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            For Customer: {{ $waiver->loan->customer->full_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Requested by {{ $waiver->requester->name }} on {{ $waiver->created_at->format('M d, Y') }}
                                        </p>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 border-l-2 border-gray-300 dark:border-gray-600 pl-2 italic">
                                            "{{ $waiver->reason }}"
                                        </p>
                                    </div>
                                    <div class="flex space-x-2 flex-shrink-0 ml-4">
                                        <form method="POST" action="{{ route('admin.waivers.update', $waiver) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="approved">
                                            <x-primary-button onclick="return confirm('Are you sure you want to approve this waiver?')">Approve</x-primary-button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.waivers.update', $waiver) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="rejected">
                                            <x-danger-button onclick="return confirm('Are you sure you want to reject this waiver?')">Reject</x-danger-button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400">There are no pending waiver requests.</p>
                        @endforelse
                    </div>
                     <div class="mt-4">
                        {{ $pendingWaivers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>