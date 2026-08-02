<?php

namespace App\Livewire\Forms;

use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class ArticleForm extends Form
{
    public ?Article $article = null;

    public string $title = '';

    public string $slug = '';

    public string $body = '';

    public bool $is_active = true;

    public string $tag_input = '';

    /** @var array<int, string> */
    public array $tags = [];

    public TemporaryUploadedFile|string|null $image = null;

    public function setArticle(Article $article): void
    {
        $this->article = $article;
        $this->title = $article->title;
        $this->slug = $article->slug;
        $this->body = $article->body;
        $this->is_active = (bool) $article->is_active;
        $this->tags = $article->tags->pluck('name')->map(fn ($name) => (string) $name)->values()->all();
        $this->tag_input = '';
        $this->image = null;
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
            : 'article-'.Str::lower(Str::random(8));
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
                Rule::unique('articles', 'slug')->ignore($this->article?->id),
            ],
            'body' => ['required', 'string'],
            'is_active' => ['boolean'],
            'tags' => ['array'],
            'tags.*' => ['string', 'max:100'],
            'image' => [
                $this->article ? 'nullable' : 'required',
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
            'slug' => __('general.slug'),
            'body' => __('general.body'),
            'is_active' => __('general.active'),
            'tags' => __('general.tags'),
            'image' => __('general.image'),
        ];
    }

    public function store(): Article
    {
        $this->prepareSlug();
        $this->validate();

        $article = Article::query()->create([
            'title' => $this->title,
            'slug' => $this->slug,
            'body' => $this->body,
            'is_active' => $this->is_active,
        ]);

        $this->syncTags($article);
        $this->attachImage($article);

        return $article;
    }

    public function update(Article $article): Article
    {
        $this->article = $article;
        $this->prepareSlug();
        $this->validate();

        $article->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'body' => $this->body,
            'is_active' => $this->is_active,
        ]);

        $this->syncTags($article);
        $this->attachImage($article);

        return $article->refresh();
    }

    protected function syncTags(Article $article): void
    {
        $article->syncTags($this->tags);
    }

    protected function attachImage(Article $article): void
    {
        if (! $this->image instanceof TemporaryUploadedFile) {
            return;
        }

        $article
            ->addMedia($this->image)
            ->toMediaCollection('article_image');

        $this->image = null;
    }
}
