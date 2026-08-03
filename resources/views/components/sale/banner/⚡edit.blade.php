<?php

use App\Livewire\Forms\BannerForm;
use App\Models\Banner;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    use WithFileUploads;

    public BannerForm $form;

    public int $bannerId;

    public ?string $currentImageUrl = null;

    public function mount(int $bannerId): void
    {
        $this->bannerId = $bannerId;

        $banner = Banner::query()
            ->with('media')
            ->findOrFail($bannerId);

        $this->form->setBanner($banner);
        $this->currentImageUrl = $this->imageUrl($banner);
    }

    public function save(): void
    {
        $banner = Banner::query()->findOrFail($this->bannerId);

        $this->form->update($banner);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleBannersTable');
    }

    protected function imageUrl(Banner $banner): ?string
    {
        $media = $banner->getFirstMedia('banner_image');

        if ($media === null) {
            return null;
        }

        $url = $media->getUrl('thumb');

        if ($url === '') {
            $url = $media->getUrl();
        }

        return $url !== '' ? $url : null;
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.edit_banner') }}
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
                <x-fwb.textarea
                    wire:model="form.description"
                    :label="__('general.description')"
                    rows="3"
                />
                @error('form.description')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="form.link_url"
                    :label="__('general.link_url')"
                    type="text"
                    dir="ltr"
                />
                @error('form.link_url')
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
                <x-fwb.checkbox wire:model="form.is_active" :label="__('general.active')" />
                @error('form.is_active')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-ui.file-input
                    wire:model="form.image"
                    :label="__('general.image')"
                    accept="image/jpeg,image/png,image/webp,image/avif"
                    dropzone
                    :preview="$currentImageUrl"
                />
                @error('form.image')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
