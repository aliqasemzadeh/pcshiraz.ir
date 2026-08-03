<?php

use App\Livewire\Forms\ArticleForm;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;
use Spatie\Tags\Tag;

new class extends Component
{
    use WithFileUploads;

    public ArticleForm $form;

    public ?string $selectedExistingTag = null;

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
        $this->form->store();

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleArticlesTable');
        $this->form->reset();
        $this->form->is_active = true;
        $this->form->tags = [];
        $this->selectedExistingTag = null;
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
            {{ __('general.create_article') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            @include('components.sale.article.partials.form-fields', [
                'existingTags' => $this->existingTags,
                'currentImageUrl' => null,
            ])

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
