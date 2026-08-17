<?php

use App\Enums\PriceTypeEnum;
use App\Livewire\Forms\ItemPriceForm;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Services\Sale\DigikalaPriceSyncService;
use App\Support\DigikalaPriceFetcher;
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

    public string $digikalaUrl = '';

    public ?int $digikalaVariantId = null;

    public bool $digikalaAutoSync = false;

    /** @var list<array{variant_id: int, color_id: ?int, color_title: string, price_toman: ?int, is_available: bool}> */
    public array $digikalaVariants = [];

    public function mount(Item $item): void
    {
        $this->item = $item->load(['brand', 'category', 'media']);
        $this->activeType = PriceTypeEnum::Cash->value;
        $this->form->setType($this->activeType);
        $this->form->setStockFromItem($this->item);
        $this->digikalaUrl = (string) ($item->digikala_url ?? '');
        $this->digikalaVariantId = $item->digikala_variant_id;
        $this->digikalaAutoSync = (bool) $item->digikala_auto_sync;
    }

    public function selectType(string $type): void
    {
        $this->activeType = $type;
        $this->form->setType($type);
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->form->price_type = $this->activeType;
        $this->form->store($this->item);
        $this->item->refresh();
        $this->form->setType($this->activeType);
        $this->form->setStockFromItem($this->item);

        Toaster::success(__('general.saved'));
        unset($this->activePrices, $this->priceHistory);
    }

    public function loadDigikalaVariants(): void
    {
        $this->validate([
            'digikalaUrl' => ['required', 'regex:/digikala\.com\/product\/dkp-\d+/i'],
        ], [], [
            'digikalaUrl' => __('app.digikala_url'),
        ]);

        $this->digikalaVariants = DigikalaPriceFetcher::fetchVariants($this->digikalaUrl);

        if ($this->digikalaVariants === []) {
            Toaster::error(__('app.digikala_variants_not_found'));

            return;
        }

        if ($this->digikalaVariantId === null) {
            $suggested = DigikalaPriceFetcher::suggestVariantId($this->digikalaVariants, $this->item->color_name);

            if ($suggested !== null) {
                $this->digikalaVariantId = $suggested;
            }
        }

        Toaster::success(__('app.digikala_variants_loaded'));
    }

    public function saveDigikalaSettings(): void
    {
        $this->validate([
            'digikalaUrl' => ['nullable', 'regex:/digikala\.com\/product\/dkp-\d+/i'],
            'digikalaVariantId' => ['nullable', 'integer', 'min:1'],
            'digikalaAutoSync' => ['boolean'],
        ], [], [
            'digikalaUrl' => __('app.digikala_url'),
            'digikalaVariantId' => __('app.digikala_variant'),
            'digikalaAutoSync' => __('app.digikala_auto_sync'),
        ]);

        if ($this->digikalaUrl !== '' && $this->digikalaVariants !== [] && $this->digikalaVariantId === null) {
            $this->addError('digikalaVariantId', __('app.digikala_variant_required'));

            return;
        }

        $productId = $this->digikalaUrl !== ''
            ? DigikalaPriceFetcher::extractProductId($this->digikalaUrl)
            : null;

        $this->item->update([
            'digikala_url' => $this->digikalaUrl !== '' ? $this->digikalaUrl : null,
            'digikala_product_id' => $productId,
            'digikala_variant_id' => $this->digikalaVariantId,
            'digikala_auto_sync' => $this->digikalaAutoSync,
        ]);

        $this->item->refresh();

        Toaster::success(__('general.saved'));
    }

    public function syncDigikalaNow(DigikalaPriceSyncService $syncService): void
    {
        if ($this->item->digikala_product_id === null) {
            Toaster::error(__('app.digikala_product_not_configured'));

            return;
        }

        $result = $syncService->syncItem($this->item->refresh());

        if ($result->success) {
            Toaster::success($result->message ?? __('app.digikala_sync_success'));
        } else {
            Toaster::error($result->message ?? __('app.digikala_sync_failed'));
        }

        unset($this->activePrices, $this->priceHistory);
    }

    #[Computed]
    public function digikalaVariantOptions(): array
    {
        $options = ['' => __('app.digikala_select_variant')];

        foreach ($this->digikalaVariants as $variant) {
            $label = $variant['color_title'];

            if ($variant['price_toman'] !== null) {
                $label .= ' — '.number_format($variant['price_toman']).' '.__('app.toman');
            }

            if (! $variant['is_available']) {
                $label .= ' ('.__('app.digikala_unavailable').')';
            }

            $options[(string) $variant['variant_id']] = $label;
        }

        return $options;
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

        <x-fwb.card>
            <h3 class="mb-4 text-lg font-semibold text-heading">{{ __('app.digikala_sync') }}</h3>

            <div class="space-y-4">
                <div>
                    <x-fwb.input
                        wire:model="digikalaUrl"
                        :label="__('app.digikala_url')"
                        type="url"
                        dir="ltr"
                        placeholder="https://www.digikala.com/product/dkp-..."
                    />
                    @error('digikalaUrl')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-ui.button type="button" color="blue" target="loadDigikalaVariants" wire:click="loadDigikalaVariants">
                        {{ __('app.digikala_load_variants') }}
                    </x-ui.button>
                    <x-ui.button type="button" color="teal" target="syncDigikalaNow" wire:click="syncDigikalaNow">
                        {{ __('app.digikala_sync_now') }}
                    </x-ui.button>
                </div>

                @if ($this->digikalaVariants !== [])
                    <div>
                        <x-fwb.select
                            wire:model="digikalaVariantId"
                            :label="__('app.digikala_variant')"
                            :options="$this->digikalaVariantOptions"
                        />
                        @error('digikalaVariantId')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <x-fwb.checkbox
                        wire:model="digikalaAutoSync"
                        :label="__('app.digikala_auto_sync')"
                    />
                    @error('digikalaAutoSync')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                @if ($item->digikala_last_synced_at)
                    <div class="rounded-lg border border-default bg-neutral-secondary-soft p-3 text-sm text-body">
                        <div>{{ __('app.digikala_last_synced_at') }}: {{ Jalalian::fromDateTime($item->digikala_last_synced_at)->format('Y/m/d H:i') }}</div>
                        <div>{{ __('general.status') }}: {{ __('app.digikala_sync_status_'.$item->digikala_last_sync_status) }}</div>
                        @if ($item->digikala_last_sync_message)
                            <div>{{ $item->digikala_last_sync_message }}</div>
                        @endif
                    </div>
                @endif

                <x-ui.button type="button" color="green" target="saveDigikalaSettings" wire:click="saveDigikalaSettings" class="w-full">
                    {{ __('app.digikala_save_settings') }}
                </x-ui.button>
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
                        <div>{{ __('app.stock') }}: {{ number_format((int) $item->stock) }}</div>
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
                        <x-fwb.input
                            wire:model="form.stock"
                            :label="__('app.stock')"
                            type="number"
                            min="0"
                            dir="ltr"
                        />
                        @error('form.stock')
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
