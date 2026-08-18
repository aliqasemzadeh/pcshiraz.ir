<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class DigikalaPriceFetcher
{
    /** @var array<string, ?array> */
    private static array $productCache = [];

    public static function extractProductId(string $url): ?string
    {
        if (! preg_match('/dkp-(\d+)/', $url, $matches)) {
            return null;
        }

        return $matches[1];
    }

    /**
     * @return list<array{variant_id: int, color_id: ?int, color_title: string, price_toman: ?int, is_available: bool}>
     */
    public static function fetchVariants(string $url, mixed $logger = null): array
    {
        $productId = self::extractProductId($url);

        if ($productId === null) {
            if ($logger) {
                $logger->warning('Could not extract product ID from URL');
            }

            return [];
        }

        $product = self::fetchProduct($productId, $logger);

        if ($product === null) {
            return [];
        }

        return self::parseVariantsFromProduct($product);
    }

    public static function fetchPrice(string $url, ?int $variantId = null, mixed $logger = null): ?int
    {
        $productId = self::extractProductId($url);

        if ($productId === null) {
            if ($logger) {
                $logger->warning('Could not extract product ID from URL');
            }

            return null;
        }

        if ($logger) {
            $logger->info("Extracted product ID: {$productId}");
        }

        if ($variantId !== null) {
            $variants = self::fetchVariants($url, $logger);

            foreach ($variants as $variant) {
                if ($variant['variant_id'] === $variantId && $variant['price_toman'] !== null) {
                    if ($logger) {
                        $logger->info("Price found for variant {$variantId}: {$variant['price_toman']} Toman");
                    }

                    return $variant['price_toman'];
                }
            }

            if ($logger) {
                $logger->warning("Variant {$variantId} not found or has no price");
            }

            return null;
        }

        if ($logger) {
            $logger->info('Trying API method...');
        }
        $price = self::tryApiMethod($productId, $logger);
        if ($price) {
            if ($logger) {
                $logger->info("Price found via API method: {$price} Toman");
            }

            return $price;
        }

        if ($logger) {
            $logger->info('Trying web API method...');
        }
        $price = self::tryWebApiMethod($productId, $logger);
        if ($price) {
            if ($logger) {
                $logger->info("Price found via web API method: {$price} Toman");
            }

            return $price;
        }

        if ($logger) {
            $logger->info('Trying HTML scraping method...');
        }
        $price = self::tryHtmlScraping($productId, $logger);
        if ($price) {
            if ($logger) {
                $logger->info("Price found via HTML scraping: {$price} Toman");
            }

            return $price;
        }

        if ($logger) {
            $logger->warning('All methods failed to fetch price');
        }

        return null;
    }

    /**
     * @param  list<array{variant_id: int, color_id: ?int, color_title: string, price_toman: ?int, is_available: bool}>  $variants
     */
    public static function suggestVariantId(array $variants, ?string $colorName): ?int
    {
        if ($colorName === null || trim($colorName) === '' || $variants === []) {
            return null;
        }

        $normalized = mb_strtolower(trim($colorName));

        foreach ($variants as $variant) {
            if (mb_strtolower(trim($variant['color_title'])) === $normalized) {
                return $variant['variant_id'];
            }
        }

        foreach ($variants as $variant) {
            $title = mb_strtolower(trim($variant['color_title']));

            if (str_contains($title, $normalized) || str_contains($normalized, $title)) {
                return $variant['variant_id'];
            }
        }

        return null;
    }

    public static function fetchProduct(string $productId, mixed $logger = null): ?array
    {
        if (array_key_exists($productId, self::$productCache)) {
            return self::$productCache[$productId];
        }

        $data = self::requestProductJson($productId, $logger);

        if ($data === null) {
            $data = self::requestProductHtml($productId, $logger);
        }

        self::$productCache[$productId] = $data;

        return $data;
    }

    /**
     * @return list<array{variant_id: int, color_id: ?int, color_title: string, price_toman: ?int, is_available: bool}>
     */
    private static function parseVariantsFromProduct(array $product): array
    {
        $variants = self::getNestedValue($product, 'variants')
            ?? self::getNestedValue($product, 'product.variants')
            ?? [];

        if (! is_array($variants)) {
            return [];
        }

        $result = [];

        foreach ($variants as $variant) {
            if (! is_array($variant)) {
                continue;
            }

            $variantId = $variant['id'] ?? null;

            if (! is_numeric($variantId)) {
                continue;
            }

            $color = is_array($variant['color'] ?? null) ? $variant['color'] : [];
            $colorTitle = trim((string) ($color['title'] ?? $color['name'] ?? ''));
            $colorId = isset($color['id']) && is_numeric($color['id']) ? (int) $color['id'] : null;
            $price = self::extractVariantPriceToman($variant);
            $status = (string) ($variant['status'] ?? 'marketable');

            $result[] = [
                'variant_id' => (int) $variantId,
                'color_id' => $colorId,
                'color_title' => $colorTitle !== '' ? $colorTitle : __('app.digikala_default_variant'),
                'price_toman' => $price,
                'is_available' => $status === 'marketable',
            ];
        }

        return $result;
    }

    private static function extractVariantPriceToman(array $variant): ?int
    {
        $price = self::getNestedValue($variant, 'price.selling_price')
            ?? self::getNestedValue($variant, 'price.sellingPrice')
            ?? self::getNestedValue($variant, 'price.rrp_price');

        if (! is_numeric($price)) {
            return null;
        }

        return (int) $price;
    }

    private static function requestProductJson(string $productId, mixed $logger = null): ?array
    {
        $endpoints = [
            "https://api.digikala.com/v1/product/{$productId}/",
            "https://www.digikala.com/api/v1/product/{$productId}/",
            "https://api.digikala.com/v2/product/{$productId}/",
            "https://www.digikala.com/api/v2/product/{$productId}/",
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders(self::jsonHeaders())
                    ->timeout(10)
                    ->get($endpoint);

                if (! $response->successful()) {
                    if ($logger) {
                        $logger->warning("Product API {$endpoint} failed with status: {$response->status()}");
                    }

                    continue;
                }

                $data = $response->json();

                if (! is_array($data)) {
                    continue;
                }

                $product = self::extractProductNode($data);

                if ($product !== null) {
                    return $product;
                }
            } catch (\Exception $e) {
                if ($logger) {
                    $logger->warning("Product API {$endpoint} exception: {$e->getMessage()}");
                }
            }
        }

        return null;
    }

    private static function requestProductHtml(string $productId, mixed $logger = null): ?array
    {
        try {
            $response = Http::withHeaders(self::htmlHeaders())
                ->timeout(15)
                ->get("https://www.digikala.com/product/dkp-{$productId}/");

            if (! $response->successful()) {
                if ($logger) {
                    $logger->warning("HTML request failed with status: {$response->status()}");
                }

                return null;
            }

            $html = $response->body();

            if (preg_match('/<script[^>]*id=["\']__NEXT_DATA__["\'][^>]*>(.+?)<\/script>/s', $html, $matches)) {
                $jsonData = json_decode($matches[1], true);

                if (is_array($jsonData)) {
                    return self::extractProductNode($jsonData)
                        ?? self::getNestedValue($jsonData, 'props.pageProps.product');
                }
            }
        } catch (\Exception $e) {
            if ($logger) {
                $logger->warning("HTML scraping exception: {$e->getMessage()}");
            }
        }

        return null;
    }

    private static function extractProductNode(array $data): ?array
    {
        $paths = [
            'data.product',
            'product',
            'props.pageProps.product',
        ];

        foreach ($paths as $path) {
            $product = self::getNestedValue($data, $path);

            if (is_array($product)) {
                return $product;
            }
        }

        return null;
    }

    private static function tryApiMethod(string $productId, mixed $logger = null): ?int
    {
        $product = self::fetchProduct($productId, $logger);

        if ($product === null) {
            return null;
        }

        $paths = [
            'default_variant.price.selling_price',
            'defaultVariant.price.selling_price',
            'price.selling_price',
        ];

        foreach ($paths as $path) {
            $price = self::getNestedValue($product, $path);

            if ($price && is_numeric($price)) {
                return (int) $price;
            }
        }

        $variants = self::parseVariantsFromProduct($product);

        foreach ($variants as $variant) {
            if ($variant['price_toman'] !== null && $variant['is_available']) {
                return $variant['price_toman'];
            }
        }

        return null;
    }

    private static function tryWebApiMethod(string $productId, mixed $logger = null): ?int
    {
        return self::tryApiMethod($productId, $logger);
    }

    private static function tryHtmlScraping(string $productId, mixed $logger = null): ?int
    {
        try {
            $response = Http::withHeaders(self::htmlHeaders())
                ->timeout(15)
                ->get("https://www.digikala.com/product/dkp-{$productId}/");

            if (! $response->successful()) {
                if ($logger) {
                    $logger->warning("HTML request failed with status: {$response->status()}");
                }

                return null;
            }

            $html = $response->body();

            if (preg_match('/<script[^>]*id=["\']__NEXT_DATA__["\'][^>]*>(.+?)<\/script>/s', $html, $matches)) {
                $jsonData = json_decode($matches[1], true);

                if (is_array($jsonData)) {
                    $paths = [
                        'props.pageProps.product.default_variant.price.selling_price',
                        'props.pageProps.product.price.selling_price',
                        'props.pageProps.product.defaultVariant.price.selling_price',
                        'props.pageProps.product.price.sellingPrice',
                    ];

                    foreach ($paths as $path) {
                        $value = self::getNestedValue($jsonData, $path);

                        if ($value && is_numeric($value)) {
                            return (int) $value;
                        }
                    }
                }
            }

            $prices = self::extractPricesFromHtml($html, $logger);

            if ($prices !== []) {
                $minPrice = min($prices);

                if ($logger) {
                    $logger->info('Found '.count($prices)." prices, returning minimum: {$minPrice}");
                }

                return $minPrice;
            }

            $patterns = [
                '/"selling_price"\s*:\s*(\d+)/',
                '/"sellingPrice"\s*:\s*(\d+)/',
                '/"price"\s*:\s*(\d+)/',
                '/"final_price"\s*:\s*(\d+)/',
                '/"finalPrice"\s*:\s*(\d+)/',
                '/price["\']?\s*[:=]\s*["\']?(\d{4,})/',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    $price = (int) $matches[1];

                    if ($price >= 1000 && $price <= 100000000) {
                        return $price;
                    }
                }
            }

            if (preg_match('/data-price=["\'](\d+)["\']/', $html, $matches)) {
                $price = (int) $matches[1];

                if ($price >= 1000 && $price <= 100000000) {
                    return $price;
                }
            }
        } catch (\Exception $e) {
            if ($logger) {
                $logger->warning("HTML scraping exception: {$e->getMessage()}");
            }
        }

        return null;
    }

    /**
     * @return list<int>
     */
    private static function extractPricesFromHtml(string $html, mixed $logger = null): array
    {
        $prices = [];

        if (preg_match('/<span[^>]*data-testid=["\']price-no-discount["\'][^>]*>([^<]+)<\/span>/i', $html, $matches)) {
            $priceText = trim($matches[1]);
            $price = self::parsePersianPrice($priceText);

            if ($price && $price >= 1000 && $price <= 100000000) {
                $prices[] = $price;

                if ($logger) {
                    $logger->info("Found main price: {$priceText} -> {$price}");
                }
            }
        }

        $sellerPricePattern = '/<div[^>]*styles_SellerListItemDesktop[^>]*>.*?<span[^>]*text-h4[^>]*text-neutral-800[^>]*>([^<]+)<\/span>/is';

        if (preg_match_all($sellerPricePattern, $html, $sellerMatches, PREG_SET_ORDER)) {
            foreach ($sellerMatches as $match) {
                $priceText = trim($match[1]);
                $price = self::parsePersianPrice($priceText);

                if ($price && $price >= 1000 && $price <= 100000000) {
                    $prices[] = $price;

                    if ($logger) {
                        $logger->info("Found seller price: {$priceText} -> {$price}");
                    }
                }
            }
        }

        $alternativePattern = '/<div[^>]*min-w-\[380px\][^>]*>.*?<span[^>]*text-h4[^>]*text-neutral-800[^>]*>([^<]+)<\/span>/is';

        if (preg_match_all($alternativePattern, $html, $altMatches, PREG_SET_ORDER)) {
            foreach ($altMatches as $match) {
                $priceText = trim($match[1]);
                $price = self::parsePersianPrice($priceText);

                if ($price && $price >= 1000 && $price <= 100000000 && ! in_array($price, $prices, true)) {
                    $prices[] = $price;

                    if ($logger) {
                        $logger->info("Found alternative seller price: {$priceText} -> {$price}");
                    }
                }
            }
        }

        return array_values(array_unique($prices));
    }

    private static function parsePersianPrice(string $priceText): ?int
    {
        $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $normalized = str_replace($persianDigits, $englishDigits, $priceText);
        $normalized = preg_replace('/[,\s]/', '', $normalized);

        if (preg_match('/(\d+)/', $normalized, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private static function jsonHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'application/json',
            'Accept-Language' => 'fa-IR,fa;q=0.9',
            'Referer' => 'https://www.digikala.com/',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function htmlHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language' => 'fa-IR,fa;q=0.9,en-US;q=0.8,en;q=0.7',
            'Referer' => 'https://www.digikala.com/',
        ];
    }

    private static function getNestedValue(array $array, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (! is_array($value) || ! array_key_exists($key, $value)) {
                return null;
            }

            $value = $value[$key];
        }

        return $value;
    }
}
