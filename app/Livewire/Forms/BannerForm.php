<?php

namespace App\Livewire\Forms;

use App\Models\Banner;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class BannerForm extends Form
{
    public ?Banner $banner = null;

    public string $title = '';

    public ?string $description = null;

    public string $link_url = '';

    public int $sort_order = 0;

    public bool $is_active = true;

    public TemporaryUploadedFile|string|null $image = null;

    public function setBanner(Banner $banner): void
    {
        $this->banner = $banner;
        $this->title = $banner->title;
        $this->description = $banner->description;
        $this->link_url = $banner->link_url;
        $this->sort_order = $banner->sort_order;
        $this->is_active = (bool) $banner->is_active;
        $this->image = null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'link_url' => ['required', 'string', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => [
                $this->banner ? 'nullable' : 'required',
                'file',
                'max:5120',
                'mimetypes:image/jpeg,image/png,image/webp,image/avif',
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
            'description' => __('general.description'),
            'link_url' => __('general.link_url'),
            'sort_order' => __('general.sort_order'),
            'is_active' => __('general.active'),
            'image' => __('general.image'),
        ];
    }

    public function store(): Banner
    {
        $this->validate();

        $banner = Banner::query()->create([
            'title' => $this->title,
            'description' => $this->description,
            'link_url' => $this->link_url,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ]);

        $this->attachImage($banner);

        return $banner;
    }

    public function update(Banner $banner): Banner
    {
        $this->banner = $banner;
        $this->validate();

        $banner->update([
            'title' => $this->title,
            'description' => $this->description,
            'link_url' => $this->link_url,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ]);

        $this->attachImage($banner);

        return $banner->refresh();
    }

    protected function attachImage(Banner $banner): void
    {
        if (! $this->image instanceof TemporaryUploadedFile) {
            return;
        }

        $banner
            ->addMedia($this->image)
            ->toMediaCollection('banner_image');

        $this->image = null;
    }
}
