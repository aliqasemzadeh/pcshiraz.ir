<?php

namespace App\Services\Shop;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductSearchService
{
    /**
     * @return Collection<int, Item>
     */
    public function search(string $q, int $limit = 10): Collection
    {
        $term = trim($q);

        if ($term === '') {
            return collect();
        }

        $like = '%'.$term.'%';

        return Item::query()
            ->active()
            ->where(function (Builder $inner) use ($like): void {
                $inner->where('title', 'like', $like)
                    ->orWhere('color_name', 'like', $like);
            })
            ->with(['brand', 'activeCashPrice', 'media'])
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }
}
