<?php

use App\Services\Sale\Catalog\HamrahtelProductImporter;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use RuntimeException;

new class extends Component
{
    public string $url = '';

    public function fetch(HamrahtelProductImporter $importer): void
    {
        $this->validate([
            'url' => ['required', 'string', 'max:2048'],
        ], [], [
            'url' => __('general.import_url'),
        ]);

        try {
            $data = $importer->import($this->url);
        } catch (RuntimeException $exception) {
            Toaster::error($exception->getMessage());

            return;
        }

        $this->dispatch('modal-close');
        $this->dispatch('panels.sale.catalog.item.create.assign-data', data: $data);
        $this->dispatch('modal-open', modal: 'sale.catalog.item.create');
        $this->reset('url');

        Toaster::success(__('general.saved'));
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
