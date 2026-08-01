@props([
    'position' => 'right',
])

<x-livewire-modal::modal
    :attributes="$attributes->class(['relative h-full rounded-2xl'])"
    :position="$position"
>
    <button
        type="button"
        class="absolute top-3 end-3 z-10 inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
        x-on:click="$dispatch('modal-close')"
        aria-label="{{ __('general.close') }}"
    >
        <x-lucide-x class="h-5 w-5" />
    </button>

    {{ $slot }}
</x-livewire-modal::modal>
