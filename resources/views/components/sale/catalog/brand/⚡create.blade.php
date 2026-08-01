<?php

use App\Livewire\Forms\BrandForm;
use App\Services\Shop\CategoryMenuService;
use App\Support\CurrentDomain;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    use WithFileUploads;

    public BrandForm $form;

    public function save(CategoryMenuService $categoryMenuService): void
    {
        $domain = CurrentDomain::get();

        if ($domain === null) {
            Toaster::error(__('general.error'));

            return;
        }

        $this->form->store($domain);
        $categoryMenuService->forget($domain);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleCatalogBrandsTable');
        $this->form->reset();
        $this->form->sort_order = 0;
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.create_brand') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            <div>
                <x-fwb.input
                    wire:model="form.title"
                    :label="__('general.title')"
                    type="text"
                />
                @error('form.title')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="form.slug"
                    :label="__('general.slug')"
                    type="text"
                    dir="ltr"
                />
                @error('form.slug')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="form.seo_title"
                    :label="__('general.seo_title')"
                    type="text"
                />
                @error('form.seo_title')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="form.sort_order"
                    :label="__('general.sort_order')"
                    type="number"
                    min="0"
                />
                @error('form.sort_order')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.file-input
                    wire:model="form.logo"
                    :label="__('general.logo')"
                    accept="image/jpeg,image/png,image/webp,image/avif,image/svg+xml"
                />
                @error('form.logo')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-teal-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-300 dark:bg-teal-600 dark:hover:bg-teal-700 dark:focus:ring-teal-800"
            >
                {{ __('general.save') }}
            </button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
