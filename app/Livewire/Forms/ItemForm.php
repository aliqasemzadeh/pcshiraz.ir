<?php

namespace App\Livewire\Forms;

use App\Enums\ItemTypeEnum;
use App\Models\Item;
use App\Services\Shop\CatalogCache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;
use Throwable;

class ItemForm extends Form
{
    public ?Item $item = null;

    public int|string|null $brand_id = null;

    public int|string|null $category_id = null;

    public string $item_type = ItemTypeEnum::Product->value;

    public int|string|null $group_id = null;

    public bool $is_main = true;

    public bool $is_active = true;

    public bool $is_purchasable = true;

    public int|string $stock = 1;

    public bool $is_contact_price = false;

    public string $title = '';

    public string $slug = '';

    public ?string $description = null;

    public ?string $color_code = null;

    public ?string $color_name = null;

    public int|string|null $weight = null;

    public int|string|null $length = null;

    public int|string|null $width = null;

    public int|string|null $height = null;

    public ?string $seo_title = null;

    public ?string $meta_description = null;

    public TemporaryUploadedFile|string|null $product_image = null;

    public ?string $remote_image_url = null;

    /** @var list<string> */
    public array $tags = [];

    public string $tag_input = '';

    public function setItem(Item $item): void
    {
        $this->item = $item;
        $this->brand_id = $item->brand_id;
        $this->category_id = $item->category_id;
        $this->item_type = $item->item_type instanceof ItemTypeEnum
            ? $item->item_type->value
            : (string) $item->item_type;
        $this->group_id = $item->group_id;
        $this->is_main = (bool) $item->is_main;
        $this->is_active = (bool) $item->is_active;
        $this->is_purchasable = (bool) $item->is_purchasable;
        $this->stock = (int) $item->stock;
        $this->is_contact_price = (bool) $item->is_contact_price;
        $this->title = $item->title;
        $this->slug = $item->slug;
        $this->description = $item->description;
        $this->color_code = $item->color_code;
        $this->color_name = $item->color_name;
        $this->weight = $item->weight;
        $this->length = $item->length;
        $this->width = $item->width;
        $this->height = $item->height;
        $this->seo_title = $item->seo_title;
        $this->meta_description = $item->meta_description;
        $this->product_image = null;
        $this->remote_image_url = null;
        $this->tags = $item->tags->pluck('name')->map(fn ($name) => (string) $name)->values()->all();
        $this->tag_input = '';
    }

    /**
     * @param  array{
     *     title?: string,
     *     slug?: string,
     *     description?: ?string,
     *     seo_title?: ?string,
     *     meta_description?: ?string,
     *     image_url?: ?string,
     *     color_name?: ?string,
     *     color_code?: ?string
     * }  $data
     */
    public function fillFromImport(array $data): void
    {
        $this->title = $data['title'] ?? $this->title;
        $this->slug = $data['slug'] ?? $this->slug;
        $this->description = $data['description'] ?? $this->description;
        $this->seo_title = $data['seo_title'] ?? $this->seo_title;
        $this->meta_description = $data['meta_description'] ?? $this->meta_description;
        $this->color_name = $data['color_name'] ?? $this->color_name;
        $this->color_code = $data['color_code'] ?? $this->color_code;
        $this->remote_image_url = $data['image_url'] ?? null;
        $this->product_image = null;
    }

    public function addTag(): void
    {
        $tag = trim($this->tag_input);

        if ($tag === '') {
            return;
        }

        if (! in_array($tag, $this->tags, true)) {
            $this->tags[] = $tag;
        }

        $this->tag_input = '';
    }

    public function removeTag(string $tag): void
    {
        $this->tags = array_values(array_filter(
            $this->tags,
            fn (string $existing) => $existing !== $tag,
        ));
    }

    public function prepareSlug(): void
    {
        $this->slug = trim($this->slug);

        if ($this->slug !== '') {
            return;
        }

        $generated = Str::slug($this->title);

        $this->slug = $generated !== ''
            ? $generated
            : 'item-'.Str::lower(Str::random(8));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
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
            'item_type' => ['required', Rule::enum(ItemTypeEnum::class)],
            'group_id' => [
                'nullable',
                'integer',
                Rule::exists('items', 'group_id')->whereNull('deleted_at'),
            ],
            'is_main' => ['boolean'],
            'is_active' => ['boolean'],
            'is_purchasable' => ['boolean'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_contact_price' => ['boolean'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items', 'slug')
                    ->whereNull('deleted_at')
                    ->ignore($this->item?->id),
            ],
            'description' => ['nullable', 'string'],
            'color_code' => ['nullable', 'string', 'max:32'],
            'color_name' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'integer', 'min:0'],
            'length' => ['nullable', 'integer', 'min:0'],
            'width' => ['nullable', 'integer', 'min:0'],
            'height' => ['nullable', 'integer', 'min:0'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'product_image' => [
                'nullable',
                'file',
                'max:4096',
                'mimetypes:image/jpeg,image/png,image/webp,image/avif',
            ],
            'remote_image_url' => ['nullable', 'url', 'max:2048'],
            'tags' => ['array'],
            'tags.*' => ['string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'brand_id' => __('general.brand'),
            'category_id' => __('general.category'),
            'item_type' => __('general.item_type'),
            'group_id' => __('general.group'),
            'is_main' => __('general.is_main'),
            'is_active' => __('app.is_active'),
            'is_purchasable' => __('app.is_purchasable'),
            'stock' => __('app.stock'),
            'is_contact_price' => __('app.is_contact_price'),
            'title' => __('general.title'),
            'slug' => __('general.slug'),
            'description' => __('general.description'),
            'color_code' => __('general.color_code'),
            'color_name' => __('general.color_name'),
            'weight' => __('general.weight'),
            'length' => __('general.length'),
            'width' => __('general.width'),
            'height' => __('general.height'),
            'seo_title' => __('general.seo_title'),
            'meta_description' => __('general.meta_description'),
            'product_image' => __('general.product_image'),
            'tags' => __('general.tags'),
        ];
    }

    public function store(): Item
    {
        $this->normalizeNullableFields();
        $this->prepareSlug();
        $this->syncPurchasableFromStock();
        $this->validate();

        $item = Item::query()->create([
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'item_type' => $this->item_type,
            'group_id' => $this->group_id,
            'is_main' => $this->is_main,
            'is_active' => $this->is_active,
            'is_purchasable' => $this->is_purchasable,
            'stock' => (int) $this->stock,
            'is_contact_price' => $this->is_contact_price,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'color_code' => $this->color_code,
            'color_name' => $this->color_name,
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'seo_title' => $this->seo_title,
            'meta_description' => $this->meta_description,
        ]);

        $this->syncTags($item);
        $this->attachImage($item);
        CatalogCache::forgetAll();

        return $item->refresh();
    }

    public function update(Item $item): Item
    {
        $this->item = $item;
        $this->normalizeNullableFields();
        $this->prepareSlug();
        $this->syncPurchasableFromStock();
        $this->validate();

        $item->update([
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'item_type' => $this->item_type,
            'group_id' => $this->group_id ?: $item->group_id,
            'is_main' => $this->is_main,
            'is_active' => $this->is_active,
            'is_purchasable' => $this->is_purchasable,
            'stock' => (int) $this->stock,
            'is_contact_price' => $this->is_contact_price,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'color_code' => $this->color_code,
            'color_name' => $this->color_name,
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'seo_title' => $this->seo_title,
            'meta_description' => $this->meta_description,
        ]);

        $this->syncTags($item);
        $this->attachImage($item);
        CatalogCache::forgetAll();

        return $item->refresh();
    }

    protected function normalizeNullableFields(): void
    {
        foreach (['brand_id', 'category_id', 'group_id', 'weight', 'length', 'width', 'height', 'stock'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }

        if ($this->stock === null) {
            $this->stock = 0;
        }

        if ($this->group_id === 0) {
            $this->group_id = null;
        }

        foreach (['description', 'color_code', 'color_name', 'seo_title', 'meta_description', 'remote_image_url'] as $field) {
            if ($this->{$field} === '') {
                $this->{$field} = null;
            }
        }
    }

    protected function syncPurchasableFromStock(): void
    {
        if ((int) $this->stock === 0) {
            $this->is_purchasable = false;
        }
    }

    protected function syncTags(Item $item): void
    {
        $item->syncTags($this->tags);
    }

    protected function attachImage(Item $item): void
    {
        if ($this->product_image instanceof TemporaryUploadedFile) {
            $item
                ->addMedia($this->product_image)
                ->toMediaCollection('product_image');

            $this->product_image = null;
            $this->remote_image_url = null;

            return;
        }

        if (! is_string($this->remote_image_url) || $this->remote_image_url === '') {
            return;
        }

        try {
            $item
                ->addMediaFromUrl($this->remote_image_url)
                ->toMediaCollection('product_image');
        } catch (Throwable) {
            // Remote image is best-effort; form fields are already saved.
        }

        $this->remote_image_url = null;
    }
}
