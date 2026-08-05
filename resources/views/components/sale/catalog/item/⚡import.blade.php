<?php

use App\Models\Brand;
use App\Models\Category;
use App\Services\Sale\Catalog\HamrahtelProductImporter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int|string|null $brand_id = null;

    public int|string|null $category_id = null;

    public string $url = '';

    public function fetch(HamrahtelProductImporter $importer): void
    {
        $this->validate([
            'brand_id' => [
                'required',
                'integer',
                Rule::exists('brands', 'id')->whereNull('deleted_at'),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->whereNull('deleted_at'),
            ],
            'url' => ['required', 'string', 'max:2048'],
        ], [], [
            'brand_id' => __('general.brand'),
            'category_id' => __('general.category'),
            'url' => __('general.import_url'),
        ]);

        try {
            $data = $importer->import($this->url);
        } catch (\RuntimeException $exception) {
            Toaster::error($exception->getMessage());

            return;
        }

        $this->dispatch('modal-close');
        $this->dispatch('modal-open', modal: 'sale.catalog.item.create', props: [
            'imported' => array_merge($data, [
                'brand_id' => (int) $this->brand_id,
                'category_id' => (int) $this->category_id,
            ]),
        ]);
        $this->reset('url');

        Toaster::success(__('general.saved'));
    }

    #[Computed]
    public function brands(): array
    {
        return ['' => __('general.select_brand')] + Brand::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    #[Computed]
    public function categories(): array
    {
        return ['' => __('general.select_category')] + Category::query()
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::modal
        position="center"
        class="w-full max-w-lg overflow-auto rounded-lg bg-white p-5 dark:bg-gray-800"
    >
        <h3 class="mb-4 text-lg font-semibold text-heading">
            {{ __('general.import_from_hamrahtel') }}
        </h3>

        <form wire:submit="fetch" class="space-y-4">
            <div>
                <x-fwb.select
                    wire:model="brand_id"
                    :label="__('general.brand')"
                    :options="$this->brands"
                />
                @error('brand_id')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.select
                    wire:model="category_id"
                    :label="__('general.category')"
                    :options="$this->categories"
                />
                @error('category_id')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="url"
                    :label="__('general.import_url')"
                    type="url"
                    dir="ltr"
                    placeholder="https://hamrahtel.com/products/..."
                />
                @error('url')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <x-ui.button type="button" color="light" outline :loading="false" x-modal:close>
                    {{ __('general.cancel') }}
                </x-ui.button>
                <x-ui.button type="submit" color="teal" target="fetch">
                    {{ __('general.fetch') }}
                </x-ui.button>
            </div>
        </form>
    </x-livewire-modal::modal>
</x-livewire-modal::stack>
