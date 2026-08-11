<?php

use App\Models\UserAddress;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirectRoute('login', navigate: true);
        }
    }

    #[Computed]
    public function addresses()
    {
        return UserAddress::query()
            ->where('user_id', Auth::id())
            ->with(['province', 'city'])
            ->latest('id')
            ->paginate(config('main.per_page', 30));
    }

    #[On('shop.profile.address.saved')]
    #[On('shop.profile.address.deleted')]
    public function refreshAddresses(): void
    {
        unset($this->addresses);
        $this->resetPage();
    }
};
?>

<div class="flex min-h-[60vh] flex-col gap-6">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('app.my_addresses') }}</h1>
                <a
                    href="{{ route('profile') }}"
                    wire:navigate
                    class="mt-2 inline-flex items-center gap-1 text-sm text-brand hover:underline"
                >
                    <x-lucide-arrow-right class="h-4 w-4 rtl:rotate-180" />
                    {{ __('general.profile_settings') }}
                </a>
            </div>

            <x-ui.button
                type="button"
                color="green"
                :loading="false"
                x-modal:open="{ modal: 'user-address.create' }"
            >
                <x-slot:icon>
                    <x-lucide-plus class="me-2 h-4 w-4" />
                </x-slot:icon>
                {{ __('app.create_address') }}
            </x-ui.button>
        </div>

        @if ($this->addresses->isEmpty())
            <div class="mt-8 rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600 dark:text-gray-400">
                {{ __('app.no_addresses') }}
            </div>
        @else
            <div class="mt-6 space-y-4">
                @foreach ($this->addresses as $address)
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700" wire:key="user-address-{{ $address->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 space-y-1">
                                <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ $address->title }}</h2>
                                <p class="text-sm text-gray-600 dark:text-gray-300">
                                    {{ $address->province?->name }} / {{ $address->city?->name }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400" dir="ltr">
                                    {{ __('app.postal_code') }}: {{ $address->postal_code }}
                                </p>
                                <p class="text-sm text-gray-700 dark:text-gray-200">{{ $address->address }}</p>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <x-ui.button
                                    type="button"
                                    size="icon"
                                    color="blue"
                                    :loading="false"
                                    x-on:click="Livewire.dispatch('modal-open', { modal: 'user-address.edit', props: { addressId: {{ $address->id }} } })"
                                    :title="__('general.edit')"
                                >
                                    <x-lucide-pencil class="h-4 w-4" />
                                </x-ui.button>
                                <x-ui.button
                                    type="button"
                                    size="icon"
                                    color="red"
                                    :loading="false"
                                    x-on:click="Livewire.dispatch('modal-open', { modal: 'user-address.delete', props: { addressId: {{ $address->id }} } })"
                                    :title="__('general.delete')"
                                >
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </x-ui.button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $this->addresses->links() }}
            </div>
        @endif
    </div>
</div>
