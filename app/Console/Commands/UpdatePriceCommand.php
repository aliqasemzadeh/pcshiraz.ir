<?php

namespace App\Console\Commands;

use App\Jobs\UpdatePriceJob;
use App\Models\Item;
use App\Services\Sale\DigikalaPriceSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('prices:sync-digikala {--item= : Sync a single item ID} {--sync : Run synchronously without queue}')]
#[Description('Sync item cash prices from DigiKala')]
class UpdatePriceCommand extends Command
{
    public function handle(DigikalaPriceSyncService $syncService): int
    {
        $itemId = $this->option('item');

        if ($itemId !== null) {
            $item = Item::query()->find($itemId);

            if ($item === null) {
                $this->error("Item {$itemId} not found.");

                return self::FAILURE;
            }

            if ($this->option('sync')) {
                $result = $syncService->syncItem($item);
                $this->info("Item {$item->id}: {$result->status} — {$result->message}");

                return $result->success ? self::SUCCESS : self::FAILURE;
            }

            UpdatePriceJob::dispatch($item);
            $this->info("Dispatched sync job for item {$item->id}.");

            return self::SUCCESS;
        }

        $items = Item::query()->digikalaAutoSync()->get();

        if ($items->isEmpty()) {
            $this->info('No items configured for DigiKala auto sync.');

            return self::SUCCESS;
        }

        if ($this->option('sync')) {
            $count = $syncService->syncAll();
            $this->info("Synced {$count} item(s).");

            return self::SUCCESS;
        }

        foreach ($items as $item) {
            UpdatePriceJob::dispatch($item);
        }

        $this->info("Dispatched {$items->count()} sync job(s).");

        return self::SUCCESS;
    }
}
