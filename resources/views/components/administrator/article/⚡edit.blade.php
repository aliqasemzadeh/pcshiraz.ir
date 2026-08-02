<?php

use App\Livewire\Forms\ArticleForm;
use App\Models\Article;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;
use Spatie\Tags\Tag;

new class extends Component
{
    use WithFileUploads;

    public ArticleForm $form;

    public int $articleId;

    public ?string $selectedExistingTag = null;

    public ?string $currentImageUrl = null;

    public function mount(int $articleId): void
    {
        $this->articleId = $articleId;

        $article = Article::query()
            ->with(['media', 'tags'])
            ->findOrFail($articleId);

        $this->form->setArticle($article);
        $this->currentImageUrl = $this->imageUrl($article);
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

    public function save(): void
    {
        $article = Article::query()->findOrFail($this->articleId);

        $this->form->update($article);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-administratorArticlesTable');
    }

    protected function imageUrl(Article $article): ?string
    {
        $media = $article->getFirstMedia('article_image');

        if ($media === null) {
            return null;
        }

        $url = $media->getUrl('thumb');

        if ($url === '') {
            $url = $media->getUrl();
        }

        return $url !== '' ? $url : null;
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
            {{ __('general.edit_article') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            @include('components.administrator.article.partials.form-fields', [
                'existingTags' => $this->existingTags,
                'currentImageUrl' => $currentImageUrl,
            ])

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
