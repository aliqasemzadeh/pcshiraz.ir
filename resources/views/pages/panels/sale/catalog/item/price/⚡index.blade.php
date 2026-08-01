<?php

use App\Enums\PriceTypeEnum;
use App\Livewire\Forms\ItemPriceForm;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Support\CurrentDomain;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Morilog\Jalali\Jalalian;

new #[Layout('layouts.panels')] class extends Component
{
    public Item $item;

    public ItemPriceForm $form;

    public string $activeType = '';

    public function mount(Item $item): void
    {
        $domain = CurrentDomain::get();

        if ($domain === null || $item->domain_id !== $domain->id) {
            abort(404);
        }

        $this->item = $item->load(['brand', 'category', 'media']);
        $this->activeType = PriceTypeEnum::Cash->value;
        $this->form->setType($this->activeType);
    }

    public function selectType(string $type): void
    {
        $this->activeType = $type;
        $this->form->setType($type);
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $domain = CurrentDomain::get();

        if ($domain === null || $this->item->domain_id !== $domain->id) {
            Toaster::error(__('general.error'));

            return;
        }

        $this->form->price_type = $this->activeType;
        $this->form->store($this->item);
        $this->form->setType($this->activeType);

        Toaster::success(__('general.saved'));
        unset($this->activePrices, $this->priceHistory);
    }

    #[Computed]
    public function priceTypes(): array
    {
        return PriceTypeEnum::cases();
    }

    #[Computed]
    public function activePrices(): array
    {
        $prices = ItemPrice::query()
            ->where('item_id', $this->item->id)
            ->where('is_active', true)
            ->get()
            ->keyBy(fn (ItemPrice $price) => $price->price_type instanceof PriceTypeEnum
                ? $price->price_type->value
                : (string) $price->price_type);

        $result = [];

        foreach (PriceTypeEnum::cases() as $type) {
            $result[$type->value] = $prices->get($type->value);
        }

        return $result;
    }

    #[Computed]
    public function priceHistory()
    {
        return ItemPrice::query()
            ->where('item_id', $this->item->id)
            ->where('price_type', $this->activeType)
            ->orderByDesc('id')
            ->limit(20)
            ->get();
    }

    public function imageUrl(): ?string
    {
        $media = $this->item->getFirstMedia('product_image');

        if ($media === null) {
            return null;
        }

        $url = $media->getUrl('thumb') ?: $media->getUrl();

        return $url !== '' ? $url : null;
    }
};
?>

<x-slot name="title">{{ __('general.pricing') }} - {{ $item->title }} - {{ config('app.name') }}</x-slot>

<div>
    <x-fwb.breadcrumb class="mb-4">
        <x-fwb.breadcrumb.item home>{{ __('general.sale') }}</x-fwb.breadcrumb.item>
        <x-fwb.breadcrumb.item href="{{ route('panels.sale.catalog.item.index') }}">{{ __('general.items') }}</x-fwb.breadcrumb.item>
        <x-fwb.breadcrumb.item>{{ __('general.pricing') }}</x-fwb.breadcrumb.item>
    </x-fwb.breadcrumb>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-heading">{{ __('general.pricing') }}</h1>

            <x-ui.button
                type="button"
                color="light"
                outline
                :loading="false"
                href="{{ route('panels.sale.catalog.item.index') }}"
                wire:navigate
            >
                {{ __('general.items') }}
            </x-ui.button>
        </div>

        <x-fwb.card>
            <div class="flex flex-wrap items-center gap-4 p-1">
                @if ($this->imageUrl())
                    <img
                        src="{{ $this->imageUrl() }}"
                        alt="{{ $item->title }}"
                        class="h-16 w-16 rounded object-contain bg-neutral-secondary-soft p-1"
                    >
                @endif
                <div>
                    <h2 class="text-lg font-semibold text-heading">{{ $item->title }}</h2>
                    <p class="text-sm text-body">
                        {{ $item->brand?->title }} · {{ $item->category?->title }}
                        @if ($item->color_name)
                            · {{ $item->color_name }}
                        @endif
                    </p>
                </div>
            </div>
        </x-fwb.card>

        <div class="flex flex-wrap gap-2">
            @foreach ($this->priceTypes as $type)
                <x-ui.button
                    type="button"
                    :color="$activeType === $type->value ? 'blue' : 'light'"
                    :outline="$activeType !== $type->value"
                    :loading="false"
                    wire:click="selectType('{{ $type->value }}')"
                >
                    {{ $type->label() }}
                    @if (($this->activePrices[$type->value] ?? null) !== null)
                        <span class="ms-1 text-xs opacity-80">●</span>
                    @endif
                </x-ui.button>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-fwb.card>
                <h3 class="mb-4 text-lg font-semibold text-heading">
                    {{ __('general.price_type') }}: {{ PriceTypeEnum::from($activeType)->label() }}
                </h3>

                @php($current = $this->activePrices[$activeType] ?? null)

                @if ($current)
                    <div class="mb-4 rounded-lg border border-default bg-neutral-secondary-soft p-3 text-sm text-body">
                        <div>{{ __('general.price') }}: {{ number_format((float) $current->price) }}</div>
                        <div>{{ __('general.sale_price') }}: {{ number_format((float) $current->sale_price) }}</div>
                        @if ($current->sales_cap !== null)
                            <div>{{ __('general.sales_cap') }}: {{ number_format($current->sales_cap) }}</div>
                        @endif
                    </div>
                @endif

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <x-fwb.input
                            wire:model="form.price"
                            :label="__('general.price')"
                            type="number"
                            min="0"
                            step="1"
                            dir="ltr"
                        />
                        @error('form.price')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-fwb.input
                            wire:model="form.sale_price"
                            :label="__('general.sale_price')"
                            type="number"
                            min="0"
                            step="1"
                            dir="ltr"
                        />
                        @error('form.sale_price')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-fwb.input
                            wire:model="form.sales_cap"
                            :label="__('general.sales_cap')"
                            type="number"
                            min="0"
                            dir="ltr"
                        />
                        @error('form.sales_cap')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-fwb.checkbox
                            wire:model="form.is_active"
                            :label="__('general.active')"
                        />
                        @error('form.is_active')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit" color="green" target="save" class="w-full">
                        {{ __('general.save') }}
                    </x-ui.button>
                </form>
            </x-fwb.card>

            <x-fwb.card>
                <h3 class="mb-4 text-lg font-semibold text-heading">{{ __('general.price_history') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-default text-start text-body">
                                <th class="px-2 py-2 font-medium">{{ __('general.price') }}</th>
                                <th class="px-2 py-2 font-medium">{{ __('general.sale_price') }}</th>
                                <th class="px-2 py-2 font-medium">{{ __('general.active') }}</th>
                                <th class="px-2 py-2 font-medium">{{ __('general.created_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->priceHistory as $price)
                                <tr class="border-b border-default">
                                    <td class="px-2 py-2 text-heading" dir="ltr">{{ number_format((float) $price->price) }}</td>
                                    <td class="px-2 py-2 text-heading" dir="ltr">{{ number_format((float) $price->sale_price) }}</td>
                                    <td class="px-2 py-2">
                                        @if ($price->is_active)
                                            <span class="text-fg-success-strong">{{ __('general.active') }}</span>
                                        @else
                                            <span class="text-body">{{ __('general.inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-body">
                                        {{ $price->created_at ? Jalalian::fromDateTime($price->created_at)->format('Y/m/d H:i') : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-2 py-4 text-center text-body">—</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-fwb.card>
        </div>
    </div>
</div>
