<?php

use App\Livewire\Forms\CategoryForm;
use App\Models\Category;
use App\Services\Shop\CategoryMenuService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    use WithFileUploads;

    public CategoryForm $form;

    public int $categoryId;

    public ?string $currentLogoUrl = null;

    public function mount(int $categoryId): void
    {
        $this->categoryId = $categoryId;

        $category = Category::query()
            ->with('media')
            ->findOrFail($categoryId);

        $this->form->setCategory($category);
        $this->currentLogoUrl = $this->logoUrl($category);
    }

    public function save(CategoryMenuService $categoryMenuService): void
    {
        $category = Category::query()->findOrFail($this->categoryId);

        $this->form->update($category);
        $categoryMenuService->forget();

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleCatalogCategoriesTable');
    }

    protected function logoUrl(Category $category): ?string
    {
        $media = $category->getFirstMedia('logo_image');

        if ($media === null) {
            return null;
        }

        if ($media->mime_type === 'image/svg+xml') {
            $url = $media->getUrl();

            return $url !== '' ? $url : null;
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
            {{ __('general.edit_category') }}
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
                <x-fwb.checkbox
                    wire:model="form.show_on_home"
                    :label="__('app.show_on_home')"
                />
                @error('form.show_on_home')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            @if ($currentLogoUrl)
                <div class="flex items-center gap-3 rounded-lg border border-default bg-neutral-secondary-soft p-3">
                    <img
                        src="{{ $currentLogoUrl }}"
                        alt="{{ $form->title }}"
                        class="h-12 w-12 rounded object-contain"
                    >
                    <span class="text-sm text-body">{{ __('general.logo') }}</span>
                </div>
            @endif

            <div>
                <x-ui.file-input
                    wire:model="form.logo"
                    :label="__('general.logo')"
                    accept="image/jpeg,image/png,image/webp,image/avif,image/svg+xml"
                />
                @error('form.logo')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
