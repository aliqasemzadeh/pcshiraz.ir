<?php

use App\Models\Brand;
use App\Services\Shop\CategoryMenuService;
use App\Support\CurrentDomain;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int $brandId;

    public string $brandTitle = '';

    public function mount(int $brandId): void
    {
        $this->brandId = $brandId;

        $domainId = CurrentDomain::get()?->id;

        $brand = Brand::query()
            ->when($domainId, fn ($query) => $query->where('domain_id', $domainId))
            ->findOrFail($brandId);

        $this->brandTitle = $brand->title;
    }

    public function delete(CategoryMenuService $categoryMenuService): void
    {
        $domain = CurrentDomain::get();

        if ($domain === null) {
            Toaster::error(__('general.error'));

            return;
        }

        $brand = Brand::query()
            ->where('domain_id', $domain->id)
            ->findOrFail($this->brandId);

        $brand->delete();
        $categoryMenuService->forget($domain);

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
            <button
                type="button"
                x-modal:close
                class="rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
            >
                {{ __('general.cancel') }}
            </button>
            <button
                type="button"
                wire:click="delete"
                class="rounded-lg bg-red-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"
            >
                {{ __('general.delete') }}
            </button>
        </div>
    </x-livewire-modal::modal>
</x-livewire-modal::stack>
