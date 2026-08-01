<?php

namespace App\Livewire\Forms;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class CategoryForm extends Form
{
    public ?Category $category = null;

    public string $title = '';

    public string $slug = '';

    public ?string $seo_title = null;

    public int $sort_order = 0;

    public bool $show_on_home = false;

    public TemporaryUploadedFile|string|null $logo = null;

    public function setCategory(Category $category): void
    {
        $this->category = $category;
        $this->title = $category->title;
        $this->slug = $category->slug;
        $this->seo_title = $category->seo_title;
        $this->sort_order = $category->sort_order;
        $this->show_on_home = (bool) $category->show_on_home;
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
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')
                    ->whereNull('deleted_at')
                    ->ignore($this->category?->id),
            ],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'show_on_home' => ['boolean'],
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
            'show_on_home' => __('app.show_on_home'),
            'logo' => __('general.logo'),
        ];
    }

    public function store(): Category
    {
        $this->prepareSlug();
        $this->validate();

        $category = Category::query()->create([
            'title' => $this->title,
            'slug' => $this->slug,
            'seo_title' => $this->seo_title,
            'sort_order' => $this->sort_order,
            'show_on_home' => $this->show_on_home,
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
            'show_on_home' => $this->show_on_home,
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
            ->addMedia($this->logo)
            ->toMediaCollection('logo_image');

        $this->logo = null;
    }
}
