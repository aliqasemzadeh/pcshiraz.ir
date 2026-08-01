<?php

use App\Enums\ItemTypeEnum;
use App\Livewire\Forms\ItemForm;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Services\Shop\CategoryMenuService;
use App\Support\CurrentDomain;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;
use Spatie\Tags\Tag;

new class extends Component
{
    use WithFileUploads;

    public ItemForm $form;

    public int $itemId;

    public ?string $selectedExistingTag = null;

    public ?string $currentImageUrl = null;

    public function mount(int $itemId): void
    {
        $this->itemId = $itemId;

        $domainId = CurrentDomain::get()?->id;

        $item = Item::query()
            ->when($domainId, fn ($query) => $query->where('domain_id', $domainId))
            ->with(['media', 'tags'])
            ->findOrFail($itemId);

        $this->form->setItem($item);
        $this->currentImageUrl = $this->imageUrl($item);
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

    public function save(CategoryMenuService $categoryMenuService): void
    {
        $domain = CurrentDomain::get();

        if ($domain === null) {
            Toaster::error(__('general.error'));

            return;
        }

        $item = Item::query()
            ->where('domain_id', $domain->id)
            ->findOrFail($this->itemId);

        if ($this->form->group_id === null || $this->form->group_id === 0) {
            $this->form->group_id = $item->group_id;
        }

        $this->form->update($item);
        $categoryMenuService->forget($domain);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleCatalogItemsTable');
    }

    protected function imageUrl(Item $item): ?string
    {
        $media = $item->getFirstMedia('product_image');

        if ($media === null) {
            return null;
        }

        $url = $media->getUrl('thumb') ?: $media->getUrl();

        return $url !== '' ? $url : null;
    }

    #[Computed]
    public function brands(): array
    {
        $domainId = CurrentDomain::get()?->id;

        $options = ['' => __('general.select_brand')];

        if ($domainId === null) {
            return $options;
        }

        return $options + Brand::query()
            ->where('domain_id', $domainId)
            ->orderBy('title')
            ->pluck('title', 'id')
            ->all();
    }

    #[Computed]
    public function categories(): array
    {
        $domainId = CurrentDomain::get()?->id;

        $options = ['' => __('general.select_category')];

        if ($domainId === null) {
            return $options;
        }

        return $options + Category::query()
            ->where('domain_id', $domainId)
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
        $domainId = CurrentDomain::get()?->id;

        $options = ['' => __('general.no_group')];

        if ($domainId === null) {
            return $options;
        }

        $mains = Item::query()
            ->where('domain_id', $domainId)
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
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-lg overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.edit_item') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            @include('components.sale.catalog.item.partials.form-fields', [
                'brands' => $this->brands,
                'categories' => $this->categories,
                'itemTypes' => $this->itemTypes,
                'groups' => $this->groups,
                'existingTags' => $this->existingTags,
                'currentImageUrl' => $currentImageUrl,
            ])

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
