<?php

use App\Models\Item;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

new #[Layout('layouts.panels')] class extends Component
{
    use WithFileUploads;

    public Item $item;

    /** @var array<int, mixed> */
    public array $newPhotos = [];

    public function mount(Item $item): void
    {
        $this->item = $item->load(['brand', 'category', 'media']);
    }

    public function upload(): void
    {
        $this->validate([
            'newPhotos' => ['required', 'array', 'min:1'],
            'newPhotos.*' => ['file', 'image', 'max:4096', 'mimetypes:image/jpeg,image/png,image/webp,image/avif'],
        ]);

        foreach ($this->newPhotos as $photo) {
            $this->item
                ->addMedia($photo)
                ->toMediaCollection('gallery');
        }

        $this->reset('newPhotos');
        $this->item->refresh()->load('media');
        unset($this->photos);

        Toaster::success(__('general.photos_uploaded'));
    }

    public function delete(int $mediaId): void
    {
        $media = $this->item->getMedia('gallery')->firstWhere('id', $mediaId);

        if ($media === null) {
            return;
        }

        $media->delete();

        $this->item->refresh()->load('media');
        unset($this->photos);

        Toaster::success(__('general.deleted'));
    }

    #[Computed]
    public function photos(): Collection
    {
        return $this->item->getMedia('gallery');
    }

    public function imageUrl(): ?string
    {
        $media = $this->item->getFirstMedia('product_image');

        if ($media === null) {
            return null;
        }

        $url = $media->getUrl('thumb') ?: $media->getUrl();

        return $url !== '' ? $url : null;
    }
};
?>

<x-slot name="title">{{ __('general.gallery') }} - {{ $item->title }} - {{ config('app.name') }}</x-slot>

<div>
    <x-fwb.breadcrumb class="mb-4">
        <x-fwb.breadcrumb.item home>{{ __('general.sale') }}</x-fwb.breadcrumb.item>
        <x-fwb.breadcrumb.item href="{{ route('panels.sale.catalog.item.index') }}">{{ __('general.items') }}</x-fwb.breadcrumb.item>
        <x-fwb.breadcrumb.item>{{ __('general.gallery') }}</x-fwb.breadcrumb.item>
    </x-fwb.breadcrumb>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-heading">{{ __('general.gallery') }}</h1>

            <x-ui.button
                type="button"
                color="light"
                outline
                :loading="false"
                href="{{ route('panels.sale.catalog.item.index') }}"
                wire:navigate
            >
                {{ __('general.items') }}
            </x-ui.button>
        </div>

        <x-fwb.card>
            <div class="flex flex-wrap items-center gap-4 p-1">
                @if ($this->imageUrl())
                    <img
                        src="{{ $this->imageUrl() }}"
                        alt="{{ $item->title }}"
                        class="h-16 w-16 rounded object-contain bg-neutral-secondary-soft p-1"
                    >
                @endif
                <div>
                    <h2 class="text-lg font-semibold text-heading">{{ $item->title }}</h2>
                    <p class="text-sm text-body">
                        {{ $item->brand?->title }} · {{ $item->category?->title }}
                        @if ($item->color_name)
                            · {{ $item->color_name }}
                        @endif
                    </p>
                </div>
            </div>
        </x-fwb.card>

        <x-fwb.card>
            <h3 class="mb-4 text-lg font-semibold text-heading">{{ __('general.gallery_photos') }}</h3>

            <form wire:submit="upload" class="space-y-4">
                <x-ui.file-input
                    wire:model="newPhotos"
                    :label="__('general.gallery_photos')"
                    :helper="__('general.gallery_upload_help')"
                    accept="image/jpeg,image/png,image/webp,image/avif"
                    multiple
                    dropzone
                />
                @error('newPhotos')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
                @error('newPhotos.*')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror

                <x-ui.button type="submit" color="green" target="upload" class="w-full">
                    {{ __('general.upload') }}
                </x-ui.button>
            </form>
        </x-fwb.card>

        <x-fwb.card>
            <h3 class="mb-4 text-lg font-semibold text-heading">{{ __('general.gallery') }}</h3>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                @forelse ($this->photos as $media)
                    @php
                        $thumb = $media->getUrl('thumb') ?: $media->getUrl();
                    @endphp
                    <div class="group relative aspect-square overflow-hidden rounded-lg border border-default bg-neutral-secondary-soft">
                        <img
                            src="{{ $thumb }}"
                            alt="{{ $item->title }}"
                            class="h-full w-full object-contain p-2"
                        >
                        <div class="absolute end-2 top-2">
                            <x-fwb.tooltip :id="'pg-tooltip-gallery-delete-'.$media->id" placement="top">
                                <x-slot:triggerSlot>
                                    <x-ui.button
                                        type="button"
                                        size="icon"
                                        color="red"
                                        :loading="false"
                                        wire:click="delete({{ $media->id }})"
                                        wire:confirm="{{ __('general.are_you_sure') }}"
                                    >
                                        <x-lucide-trash-2 class="h-4 w-4" />
                                        <span class="sr-only">{{ __('general.delete') }}</span>
                                    </x-ui.button>
                                </x-slot:triggerSlot>
                                {{ __('general.delete') }}
                            </x-fwb.tooltip>
                        </div>
                    </div>
                @empty
                    <p class="col-span-full text-sm text-body">{{ __('general.no_gallery_photos') }}</p>
                @endforelse
            </div>
        </x-fwb.card>
    </div>
</div>
