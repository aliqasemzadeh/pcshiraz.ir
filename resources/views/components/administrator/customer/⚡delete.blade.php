<?php

use App\Models\Customer;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int $customerId;

    public string $customerLabel = '';

    public function mount(int $customerId): void
    {
        $this->customerId = $customerId;

        $customer = Customer::query()->with('user')->findOrFail($customerId);

        $this->customerLabel = trim(($customer->first_name ?? '').' '.($customer->last_name ?? ''))
            ?: ($customer->user?->mobile ?? '#'.$customer->id);
    }

    public function delete(): void
    {
        Customer::query()->findOrFail($this->customerId)->delete();

        Toaster::success(__('general.deleted'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-administratorCustomersTable');
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::modal
        position="center"
        class="w-full max-w-md overflow-auto rounded-lg bg-white p-5 dark:bg-gray-800"
    >
        <h3 class="mb-2 text-lg font-semibold text-heading">
            {{ __('general.delete_confirmation') }}
        </h3>
        <p class="mb-2 text-sm text-body">
            {{ $customerLabel }}
        </p>
        <p class="mb-6 text-sm text-body">
            {{ __('general.delete_warning_message') }}
            <br>
            {{ __('general.action_cannot_be_reversed') }}
        </p>
        <div class="flex justify-end gap-2">
            <x-ui.button type="button" color="light" outline :loading="false" x-modal:close>
                {{ __('general.cancel') }}
            </x-ui.button>
            <x-ui.button type="button" color="red" target="delete" wire:click="delete">
                {{ __('general.delete') }}
            </x-ui.button>
        </div>
    </x-livewire-modal::modal>
</x-livewire-modal::stack>
