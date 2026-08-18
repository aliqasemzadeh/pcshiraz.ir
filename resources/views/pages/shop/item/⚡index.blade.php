<?php

use App\Models\Category;
use App\Models\Item;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    #[Computed]
    public function groups()
    {
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return $categories->map(function (Category $category) {
            $items = Item::query()
                ->active()
                ->where('category_id', $category->id)
                ->with(['brand', 'activeCashPrice'])
                ->orderBy('title')
                ->get();

            return [
                'category' => $category,
                'items' => $items,
            ];
        })->filter(fn (array $group) => $group['items']->isNotEmpty())->values();
    }
};
?>

<div class="space-y-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('app.all_products') }}</h1>

    @forelse ($this->groups as $group)
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $group['category']->title }}</h2>
                <a href="{{ route('shop.category', $group['category']->shopRoute()) }}" wire:navigate class="text-sm text-brand hover:underline">
                    {{ __('general.view_all_in_category') }}
                </a>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-300">{{ __('general.title') }}</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-300">{{ __('general.brand') }}</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600 dark:text-gray-300">{{ __('app.cash_price') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach ($group['items'] as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60">
                                <td class="px-4 py-3">
                                    <a href="{{ route('shop.item', $item->shopRoute()) }}" wire:navigate class="font-medium text-gray-900 hover:text-brand dark:text-white">
                                        {{ $item->title }}
                                    </a>
                                    @if (! $item->is_purchasable)
                                        <span class="ms-2 text-xs text-rose-600">{{ __('app.not_purchasable') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $item->brand?->title ?? '—' }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                    @if ($item->activeCashPrice)
                                        {{ format_price((float) $item->activeCashPrice->sale_price) }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600">
            {{ __('app.no_products') }}
        </div>
    @endforelse
</div>
