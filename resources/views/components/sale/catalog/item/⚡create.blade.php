<?php

use App\Enums\ItemTypeEnum;
use App\Livewire\Forms\ItemForm;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Services\Sale\Catalog\ItemColorService;
use App\Services\Shop\CategoryMenuService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;
use Spatie\Tags\Tag;

new class extends Component
{
    use WithFileUploads;

    public ItemForm $form;

    public ?string $selectedExistingTag = null;

    public ?string $selectedExistingColor = null;

    public ?string $currentImageUrl = null;

    /**
     * @param  array<string, mixed>|null  $imported
     */
    public function mount(?array $imported = null): void
    {
        if ($imported !== null) {
            $this->form->fillFromImport($imported);
        }
    }

    #[On('panels.sale.catalog.item.create.assign-data')]
    public function assignImportedData(array $data): void
    {
        $this->form->fillFromImport($data);
    }

    public function addTag(): void
    {
        $this->form->addTag();
    }

    public function removeTag(string $tag): void
    {
        $this->form->removeTag($tag);
    }

    public function addExistingTag(): void
    {
        if ($this->selectedExistingTag === null || $this->selectedExistingTag === '') {
            return;
        }

        if (! in_array($this->selectedExistingTag, $this->form->tags, true)) {
            $this->form->tags[] = $this->selectedExistingTag;
        }

        $this->selectedExistingTag = null;
    }

    public function applyExistingColor(ItemColorService $colorService): void
    {
        $parsed = $colorService->parseSelectKey($this->selectedExistingColor);

        if ($parsed === null) {
            return;
        }

        $this->form->color_name = $parsed['name'];
        $this->form->color_code = $parsed['code'];
        $this->selectedExistingColor = null;
    }

    public function save(CategoryMenuService $categoryMenuService): void
    {
        if ($this->form->group_id === null || $this->form->group_id === 0 || $this->form->group_id === '') {
            $this->form->group_id = null;
        }

        $this->form->store();
        $categoryMenuService->forget();

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleCatalogItemsTable');
        $this->form->reset();
        $this->form->item_type = ItemTypeEnum::Product->value;
        $this->form->is_main = true;
        $this->form->is_active = true;
        $this->form->is_purchasable = true;
        $this->form->stock = 1;
        $this->form->tags = [];
        $this->currentImageUrl = null;
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

    #[Computed]
    public function itemTypes(): array
    {
        return ItemTypeEnum::labels();
    }

    #[Computed]
    public function groups(): array
    {
        $options = ['' => __('general.no_group')];

        $mains = Item::query()
            ->whereNotNull('group_id')
            ->where('is_main', true)
            ->orderBy('title')
            ->get(['group_id', 'title']);

        foreach ($mains as $main) {
            $options[$main->group_id] = $main->title.' (#'.$main->group_id.')';
        }

        return $options;
    }

    #[Computed]
    public function existingTags(): array
    {
        $options = ['' => __('general.tags')];

        $tags = Tag::query()
            ->orderBy('order_column')
            ->orderBy('id')
            ->get();

        foreach ($tags as $tag) {
            $name = (string) $tag->name;

            if ($name !== '') {
                $options[$name] = $name;
            }
        }

        return $options;
    }

    #[Computed]
    public function existingColors(): array
    {
        return app(ItemColorService::class)->optionsForSelect();
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-lg overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.create_item') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            @include('components.sale.catalog.item.partials.form-fields', [
                'brands' => $this->brands,
                'categories' => $this->categories,
                'itemTypes' => $this->itemTypes,
                'groups' => $this->groups,
                'existingTags' => $this->existingTags,
                'existingColors' => $this->existingColors,
                'currentImageUrl' => $currentImageUrl,
            ])

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
