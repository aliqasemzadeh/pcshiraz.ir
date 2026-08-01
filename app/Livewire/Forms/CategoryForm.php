<?php

namespace App\Livewire\Forms;

use App\Models\Category;
use App\Models\Domain;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;
use Illuminate\Support\Str;

class CategoryForm extends Form
{
    public ?Category $category = null;

    public string $title = '';

    public string $slug = '';

    public ?string $seo_title = null;

    public int $sort_order = 0;

    public TemporaryUploadedFile|string|null $logo = null;

    public function setCategory(Category $category): void
    {
        $this->category = $category;
        $this->title = $category->title;
        $this->slug = $category->slug;
        $this->seo_title = $category->seo_title;
        $this->sort_order = $category->sort_order;
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
            : 'category-'.Str::lower(Str::random(8));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $domainId = $this->category?->domain_id ?? $this->currentDomainId();

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')
                    ->where(fn ($query) => $query->where('domain_id', $domainId)->whereNull('deleted_at'))
                    ->ignore($this->category?->id),
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

    public function store(Domain $domain): Category
    {
        $this->prepareSlug();
        $this->validate();

        $category = Category::query()->create([
            'domain_id' => $domain->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'seo_title' => $this->seo_title,
            'sort_order' => $this->sort_order,
        ]);

        $this->attachLogo($category);

        return $category;
    }

    public function update(Category $category): Category
    {
        $this->category = $category;
        $this->prepareSlug();
        $this->validate();

        $category->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'seo_title' => $this->seo_title,
            'sort_order' => $this->sort_order,
        ]);

        $this->attachLogo($category);

        return $category->refresh();
    }

    protected function attachLogo(Category $category): void
    {
        if (! $this->logo instanceof TemporaryUploadedFile) {
            return;
        }

        $category
            ->addMedia($this->logo->getRealPath())
            ->usingFileName($this->logo->getClientOriginalName())
            ->toMediaCollection('logo_image');

        $this->logo = null;
    }

    protected function currentDomainId(): ?int
    {
        return \App\Support\CurrentDomain::get()?->id;
    }
}
