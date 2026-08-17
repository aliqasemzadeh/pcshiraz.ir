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

        $activeCashPrice = ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('price_type', PriceTypeEnum::Cash)
            ->where('is_active', true)
            ->first();

        if ($activeCashPrice !== null && (int) $activeCashPrice->sale_price === $price) {
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

        ItemPrice::query()->create([
            'item_id' => $item->id,
            'price_type' => PriceTypeEnum::Cash,
            'price' => $price,
            'sale_price' => $price,
            'sales_cap' => $activeCashPrice?->sales_cap,
            'is_active' => true,
            'meta' => [
                'source' => 'digikala',
                'synced_at' => now()->toIso8601String(),
            ],
        ]);

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
