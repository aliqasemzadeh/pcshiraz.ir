<?php

namespace App\Livewire\Forms;

use App\Models\Brand;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class BrandForm extends Form
{
    public ?Brand $brand = null;

    public string $title = '';

    public string $slug = '';

    public ?string $seo_title = null;

    public int $sort_order = 0;

    public TemporaryUploadedFile|string|null $logo = null;

    public function setBrand(Brand $brand): void
    {
        $this->brand = $brand;
        $this->title = $brand->title;
        $this->slug = $brand->slug;
        $this->seo_title = $brand->seo_title;
        $this->sort_order = $brand->sort_order;
        $this->logo = null;
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
            : 'brand-'.Str::lower(Str::random(8));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'slug')
                    ->whereNull('deleted_at')
                    ->ignore($this->brand?->id),
            ],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'logo' => [
                'nullable',
                'file',
                'max:2048',
                'mimetypes:image/jpeg,image/png,image/webp,image/avif,image/svg+xml',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'title' => __('general.title'),
            'slug' => __('general.slug'),
            'seo_title' => __('general.seo_title'),
            'sort_order' => __('general.sort_order'),
            'logo' => __('general.logo'),
        ];
    }

    public function store(): Brand
    {
        $this->prepareSlug();
        $this->validate();

        $brand = Brand::query()->create([
            'title' => $this->title,
            'slug' => $this->slug,
            'seo_title' => $this->seo_title,
            'sort_order' => $this->sort_order,
        ]);

        $this->attachLogo($brand);

        return $brand;
    }

    public function update(Brand $brand): Brand
    {
        $this->brand = $brand;
        $this->prepareSlug();
        $this->validate();

        $brand->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'seo_title' => $this->seo_title,
            'sort_order' => $this->sort_order,
        ]);

        $this->attachLogo($brand);

        return $brand->refresh();
    }

    protected function attachLogo(Brand $brand): void
    {
        if (! $this->logo instanceof TemporaryUploadedFile) {
            return;
        }

        $brand
            ->addMedia($this->logo)
            ->toMediaCollection('logo_image');

        $this->logo = null;
    }
}
