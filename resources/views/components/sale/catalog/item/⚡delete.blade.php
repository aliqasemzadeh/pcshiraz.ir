<?php

use App\Models\Item;
use App\Services\Shop\CategoryMenuService;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int $itemId;

    public string $itemTitle = '';

    public function mount(int $itemId): void
    {
        $this->itemId = $itemId;

        $item = Item::query()->findOrFail($itemId);

        $this->itemTitle = $item->title;
    }

    public function delete(CategoryMenuService $categoryMenuService): void
    {
        $item = Item::query()->findOrFail($this->itemId);

        $item->delete();
        $categoryMenuService->forget();

        Toaster::success(__('general.deleted'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleCatalogItemsTable');
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
            {{ $itemTitle }}
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
