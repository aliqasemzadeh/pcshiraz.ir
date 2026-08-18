<?php

namespace App\Services\Sale;

use App\DataTransferObjects\DigikalaSyncResult;
use App\Enums\PriceTypeEnum;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Support\DigikalaPriceFetcher;
use Illuminate\Support\Facades\Log;

class DigikalaPriceSyncService
{
    public function syncItem(Item $item): DigikalaSyncResult
    {
        if ($item->digikala_product_id === null || $item->digikala_product_id === '') {
            return $this->recordFailure($item, __('app.digikala_product_not_configured'));
        }

        $price = DigikalaPriceFetcher::fetchPrice(
            $item->digikala_url ?? '',
            $item->digikala_variant_id,
        );

        if ($price === null) {
            return $this->recordFailure($item, __('app.digikala_sync_failed'));
        }

        $activePrices = ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('is_active', true)
            ->whereIn('price_type', [PriceTypeEnum::Cash, PriceTypeEnum::Installment])
            ->get()
            ->keyBy(fn (ItemPrice $itemPrice) => $itemPrice->price_type instanceof PriceTypeEnum
                ? $itemPrice->price_type->value
                : (string) $itemPrice->price_type);

        $activeCashPrice = $activePrices->get(PriceTypeEnum::Cash->value);
        $activeInstallmentPrice = $activePrices->get(PriceTypeEnum::Installment->value);

        $cashMatches = $activeCashPrice !== null && (int) $activeCashPrice->sale_price === $price;
        $installmentMatches = $activeInstallmentPrice !== null && (int) $activeInstallmentPrice->sale_price === $price;

        if ($cashMatches && $installmentMatches) {
            $item->update([
                'digikala_last_synced_at' => now(),
                'digikala_last_sync_status' => 'unchanged',
                'digikala_last_sync_message' => __('app.digikala_sync_unchanged'),
            ]);

            return new DigikalaSyncResult(
                success: true,
                status: 'unchanged',
                message: __('app.digikala_sync_unchanged'),
                price: $price,
            );
        }

        if (! $cashMatches) {
            $this->createSyncedPrice($item, PriceTypeEnum::Cash, $price, $activeCashPrice?->sales_cap);
        }

        if (! $installmentMatches) {
            $this->createSyncedPrice($item, PriceTypeEnum::Installment, $price, $activeInstallmentPrice?->sales_cap);
        }

        $item->update([
            'digikala_last_synced_at' => now(),
            'digikala_last_sync_status' => 'success',
            'digikala_last_sync_message' => __('app.digikala_sync_success'),
        ]);

        return new DigikalaSyncResult(
            success: true,
            status: 'success',
            message: __('app.digikala_sync_success'),
            price: $price,
        );
    }

    public function syncAll(): int
    {
        $count = 0;

        Item::query()
            ->digikalaAutoSync()
            ->orderBy('id')
            ->each(function (Item $item) use (&$count): void {
                $result = $this->syncItem($item);

                if ($result->success) {
                    $count++;
                }

                sleep(1);
            });

        return $count;
    }

    /**
     * Persist the DigiKala Toman amount as-is. Do not run Price::toDisplay/fromDisplay (Rial ×10 / ÷10).
     */
    protected function createSyncedPrice(Item $item, PriceTypeEnum $type, int $price, mixed $salesCap): ItemPrice
    {
        return ItemPrice::query()->create([
            'item_id' => $item->id,
            'price_type' => $type,
            'price' => $price,
            'sale_price' => $price,
            'sales_cap' => $salesCap,
            'is_active' => true,
            'meta' => [
                'source' => 'digikala',
                'synced_at' => now()->toIso8601String(),
            ],
        ]);
    }

    protected function recordFailure(Item $item, string $message): DigikalaSyncResult
    {
        Log::warning('DigiKala price sync failed', [
            'item_id' => $item->id,
            'message' => $message,
        ]);

        $item->update([
            'digikala_last_synced_at' => now(),
            'digikala_last_sync_status' => 'failed',
            'digikala_last_sync_message' => $message,
        ]);

        return new DigikalaSyncResult(
            success: false,
            status: 'failed',
            message: $message,
        );
    }
}
