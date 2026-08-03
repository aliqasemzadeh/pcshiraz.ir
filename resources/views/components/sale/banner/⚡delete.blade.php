<?php

use App\Models\Banner;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int $bannerId;

    public string $bannerTitle = '';

    public function mount(int $bannerId): void
    {
        $this->bannerId = $bannerId;

        $banner = Banner::query()->findOrFail($bannerId);

        $this->bannerTitle = $banner->title;
    }

    public function delete(): void
    {
        $banner = Banner::query()->findOrFail($this->bannerId);

        $banner->clearMediaCollection('banner_image');
        $banner->delete();

        Toaster::success(__('general.deleted'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleBannersTable');
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
            {{ $bannerTitle }}
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
