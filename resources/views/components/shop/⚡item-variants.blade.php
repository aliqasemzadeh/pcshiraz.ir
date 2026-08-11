<?php

use App\Models\Item;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Component;

new #[Lazy] class extends Component
{
    public int $itemId;

    public ?int $groupId = null;

    #[Computed]
    public function variants(): Collection
    {
        if ($this->groupId === null) {
            return collect();
        }

        return Item::query()
            ->active()
            ->where('group_id', $this->groupId)
            ->where('id', '!=', $this->itemId)
            ->with(['brand', 'media', 'activeCashPrice', 'activeInstallmentPrice'])
            ->orderByDesc('is_main')
            ->limit(12)
            ->get();
    }
};
?>

@placeholder
    <div class="h-40 animate-pulse rounded-xl bg-gray-100 dark:bg-gray-800"></div>
@endplaceholder

<div>
    @if ($this->variants->isNotEmpty())
        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('general.group') }}</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($this->variants as $variant)
                    <x-shop.item-card :item="$variant" wire:key="variant-{{ $variant->id }}" />
                @endforeach
            </div>
        </section>
    @endif
</div>
