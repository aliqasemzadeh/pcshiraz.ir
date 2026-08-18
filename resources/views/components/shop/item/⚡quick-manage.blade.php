<?php

use App\Enums\PriceTypeEnum;
use App\Livewire\Forms\ItemPriceForm;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Services\Sale\DigikalaPriceSyncService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Morilog\Jalali\Jalalian;

new class extends Component
{
    public int $itemId;

    public Item $item;

    public ItemPriceForm $form;

    public string $activeType = '';

    public function mount(int $itemId): void
    {
        $this->authorizeAccess();

        $this->itemId = $itemId;
        $this->activeType = PriceTypeEnum::Cash->value;
        $this->loadItem();
        $this->form->setType($this->activeType);
        $this->prefillPriceFromActive();
    }

    public function selectType(string $type): void
    {
        $this->activeType = $type;
        $this->form->setType($type);
        $this->prefillPriceFromActive();
        $this->resetErrorBag();
    }

    public function adjustStock(int $delta): void
    {
        $this->authorizeAccess();

        $newStock = max(0, (int) $this->item->stock + $delta);

        $this->item->update([
            'stock' => $newStock,
            'is_purchasable' => $newStock > 0,
        ]);

        $this->loadItem();
        $this->form->setStockFromItem($this->item);

        Toaster::success(__('app.item_stock_updated'));
        $this->dispatch('shop.item.updated');
    }

    public function savePrice(): void
    {
        $this->authorizeAccess();

        $this->form->price_type = $this->activeType;
        $this->form->store(
            $this->item,
            alsoSetInstallment: $this->activeType === PriceTypeEnum::Cash->value,
        );

        $this->loadItem();
        $this->prefillPriceFromActive();

        Toaster::success(__('general.saved'));
        $this->dispatch('shop.item.updated');
    }

    public function syncDigikalaNow(DigikalaPriceSyncService $syncService): void
    {
        $this->authorizeAccess();

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

        $this->loadItem();
        $this->prefillPriceFromActive();
        $this->dispatch('shop.item.updated');
    }

    #[On('shop.item.updated')]
    public function refreshFromParent(): void
    {
        $this->loadItem();
        $this->prefillPriceFromActive();
    }

    #[Computed]
    public function priceTypes(): array
    {
        return [
            PriceTypeEnum::Cash,
            PriceTypeEnum::Installment,
        ];
    }

    #[Computed]
    public function activePrices(): array
    {
        $prices = ItemPrice::query()
            ->where('item_id', $this->item->id)
            ->where('is_active', true)
            ->whereIn('price_type', [PriceTypeEnum::Cash->value, PriceTypeEnum::Installment->value])
            ->get()
            ->keyBy(fn (ItemPrice $price) => $price->price_type instanceof PriceTypeEnum
                ? $price->price_type->value
                : (string) $price->price_type);

        $result = [];

        foreach ($this->priceTypes as $type) {
            $result[$type->value] = $prices->get($type->value);
        }

        return $result;
    }

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->check() && auth()->user()->can('sale.item_edit'), 403);
    }

    protected function loadItem(): void
    {
        $this->item = Item::query()
            ->with(['activeCashPrice', 'activeInstallmentPrice'])
            ->findOrFail($this->itemId);
    }

    protected function prefillPriceFromActive(): void
    {
        $current = $this->activePrices[$this->activeType] ?? null;

        if ($current !== null) {
            $this->form->price = (string) $current->price;
            $this->form->sale_price = (string) $current->sale_price;
            $this->form->sales_cap = $current->sales_cap;
        } else {
            $this->form->price = '';
            $this->form->sale_price = '';
            $this->form->sales_cap = null;
        }

        $this->form->setStockFromItem($this->item);
        $this->form->is_active = true;
    }
};
?>

<div class="sticky top-16 z-20 -mx-6 mb-6 border-b border-amber-200 bg-amber-50/95 px-4 py-3 shadow-sm backdrop-blur dark:border-amber-900/50 dark:bg-amber-950/90">
    <div class="mx-auto max-w-screen-2xl space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 text-sm font-semibold text-amber-900 dark:text-amber-200">
                <x-lucide-zap class="h-4 w-4" />
                {{ __('app.item_quick_manage') }}
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button
                    type="button"
                    size="xs"
                    color="blue"
                    :loading="false"
                    x-on:click="Livewire.dispatch('modal-open', { modal: 'sale.catalog.item.edit', props: { itemId: {{ $item->id }} } })"
                >
                    <x-lucide-pencil class="me-1.5 h-3.5 w-3.5" />
                    {{ __('app.item_edit_product') }}
                </x-ui.button>

                <x-ui.button
                    type="button"
                    size="xs"
                    color="light"
                    outline
                    :loading="false"
                    href="{{ route('panels.sale.catalog.item.price.index', $item) }}"
                    wire:navigate
                >
                    {{ __('app.item_full_pricing') }}
                </x-ui.button>
            </div>
        </div>

        <div class="flex flex-wrap items-end gap-4 lg:gap-6">
            {{-- Stock --}}
            <div class="min-w-[140px]">
                <div class="mb-1 text-xs font-medium text-amber-800 dark:text-amber-300">{{ __('app.stock') }}</div>
                <div class="flex items-center gap-2">
                    <x-ui.button
                        type="button"
                        size="icon"
                        color="zinc"
                        :loading="false"
                        :disabled="$item->stock <= 0"
                        wire:click="adjustStock(-1)"
                        title="{{ __('app.decrease_quantity') }}"
                    >
                        <x-lucide-minus class="h-4 w-4" />
                    </x-ui.button>
                    <span class="min-w-10 text-center text-lg font-bold text-gray-900 dark:text-white">
                        {{ number_format((int) $item->stock) }}
                    </span>
                    <x-ui.button
                        type="button"
                        size="icon"
                        color="teal"
                        :loading="false"
                        wire:click="adjustStock(1)"
                        title="{{ __('app.increase_quantity') }}"
                    >
                        <x-lucide-plus class="h-4 w-4" />
                    </x-ui.button>
                </div>
            </div>

            {{-- Price tabs + form --}}
            <div class="min-w-0 flex-1">
                <div class="mb-2 flex flex-wrap gap-1.5">
                    @foreach ($this->priceTypes as $type)
                        <x-ui.button
                            type="button"
                            size="xs"
                            :color="$activeType === $type->value ? 'blue' : 'light'"
                            :outline="$activeType !== $type->value"
                            :loading="false"
                            wire:click="selectType('{{ $type->value }}')"
                        >
                            {{ $type->label() }}
                            @if (($this->activePrices[$type->value] ?? null) !== null)
                                <span class="ms-1 text-[10px] opacity-80">●</span>
                            @endif
                        </x-ui.button>
                    @endforeach
                </div>

                <form wire:submit="savePrice" class="flex flex-wrap items-end gap-2">
                    <div class="w-28 sm:w-32">
                        <x-fwb.input
                            wire:model="form.price"
                            :label="__('general.price').' ('.price_unit_label().')'"
                            type="text"
                            inputmode="numeric"
                            dir="ltr"
                            class="text-sm"
                            x-mask:dynamic="\$money(\$input, '.', ',', 0)"
                            x-init="\$el.dispatchEvent(new Event('input'))"
                        />
                    </div>
                    <div class="w-28 sm:w-32">
                        <x-fwb.input
                            wire:model="form.sale_price"
                            :label="__('general.sale_price').' ('.price_unit_label().')'"
                            type="text"
                            inputmode="numeric"
                            dir="ltr"
                            class="text-sm"
                            x-mask:dynamic="\$money(\$input, '.', ',', 0)"
                            x-init="\$el.dispatchEvent(new Event('input'))"
                        />
                    </div>
                    <x-ui.button type="submit" size="sm" color="green" target="savePrice">
                        <x-lucide-save class="me-1.5 h-4 w-4" />
                        {{ __('general.save') }}
                    </x-ui.button>
                </form>
            </div>

            {{-- DigiKala --}}
            <div class="min-w-[160px]">
                <div class="mb-1 text-xs font-medium text-amber-800 dark:text-amber-300">{{ __('app.digikala_sync') }}</div>
                @if ($item->digikala_product_id)
                    <x-ui.button type="button" size="sm" color="teal" target="syncDigikalaNow" wire:click="syncDigikalaNow">
                        <x-lucide-refresh-cw class="me-1.5 h-4 w-4" />
                        {{ __('app.digikala_sync_now') }}
                    </x-ui.button>
                    @if ($item->digikala_last_synced_at)
                        <p class="mt-1 text-[11px] text-amber-700 dark:text-amber-400">
                            {{ Jalalian::fromDateTime($item->digikala_last_synced_at)->format('Y/m/d H:i') }}
                            @if (filled($item->digikala_last_sync_status))
                                · {{ __('app.digikala_sync_status_'.$item->digikala_last_sync_status) }}
                            @endif
                        </p>
                    @endif
                @else
                    <p class="text-xs text-amber-700 dark:text-amber-400">{{ __('app.digikala_product_not_configured') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
