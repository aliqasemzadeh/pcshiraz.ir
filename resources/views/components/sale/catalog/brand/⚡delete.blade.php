<?php

use App\Models\Brand;
use App\Services\Shop\CategoryMenuService;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int $brandId;

    public string $brandTitle = '';

    public function mount(int $brandId): void
    {
        $this->brandId = $brandId;

        $brand = Brand::query()->findOrFail($brandId);

        $this->brandTitle = $brand->title;
    }

    public function delete(CategoryMenuService $categoryMenuService): void
    {
        $brand = Brand::query()->findOrFail($this->brandId);

        $brand->delete();
        $categoryMenuService->forget();

        Toaster::success(__('general.deleted'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleCatalogBrandsTable');
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
            {{ $brandTitle }}
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
