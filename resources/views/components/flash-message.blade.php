@if (session('success') || session('error'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 4000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="fixed top-5 right-5 z-50 px-4 py-2 rounded-md text-white
            {{ session('success') ? 'bg-green-500' : 'bg-red-500' }}"
    >
        {{ session('success') ?? session('error') }}
    </div>
@endif