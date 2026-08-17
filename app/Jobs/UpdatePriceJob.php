<?php

namespace App\Jobs;

use App\Models\Item;
use App\Services\Sale\DigikalaPriceSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdatePriceJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    public function __construct(public Item $item) {}

    public function handle(DigikalaPriceSyncService $syncService): void
    {
        $syncService->syncItem($this->item);
    }
}
