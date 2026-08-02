<?php

namespace App\Services\Shop;

use App\Models\Banner;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BannerCache
{
    public const HOME = 'shop.home.banners.v1';

    public const TTL = 3600;

    /**
     * @return list<array{id: int, title: string, description: ?string, image_url: string, click_url: string}>
     */
    public static function home(): array
    {
        return Cache::remember(self::HOME, self::TTL, function () {
            return Banner::query()
                ->active()
                ->with('media')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(function (Banner $banner) {
                    $imageUrl = self::optimizedImageUrl($banner);

                    if ($imageUrl === null) {
                        return null;
                    }

                    return [
                        'id' => $banner->id,
                        'title' => $banner->title,
                        'description' => $banner->description,
                        'image_url' => $imageUrl,
                        'click_url' => route('banner.click', $banner),
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::HOME);
    }

    protected static function optimizedImageUrl(Banner $banner): ?string
    {
        $media = $banner->getFirstMedia('banner_image');

        if (! $media instanceof Media) {
            return null;
        }

        $url = $media->getUrl('optimized');

        if ($url === '') {
            $url = $media->getUrl();
        }

        return $url !== '' ? $url : null;
    }
}
