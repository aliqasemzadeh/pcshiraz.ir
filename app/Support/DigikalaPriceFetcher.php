<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class DigikalaPriceFetcher
{
    public static function fetchPrice(string $url, $logger = null): ?int
    {
        // Extract product ID from URL
        if (! preg_match('/dkp-(\d+)/', $url, $matches)) {
            if ($logger) {
                $logger->warning('Could not extract product ID from URL');
            }

            return null;
        }

        $productId = $matches[1];

        if ($logger) {
            $logger->info("Extracted product ID: {$productId}");
        }

        // Try multiple methods to fetch price
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

    private static function tryApiMethod(string $productId, $logger = null): ?int
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'application/json',
                'Accept-Language' => 'fa-IR,fa;q=0.9',
                'Referer' => 'https://www.digikala.com/',
            ])->timeout(10)->get("https://api.digikala.com/v1/product/{$productId}/");

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    // Try multiple paths
                    $paths = [
                        'data.product.default_variant.price.selling_price',
                        'data.product.price.selling_price',
                        'data.product.defaultVariant.price.selling_price',
                        'product.default_variant.price.selling_price',
                        'product.price.selling_price',
                    ];

                    foreach ($paths as $path) {
                        $price = self::getNestedValue($data, $path);
                        if ($price && is_numeric($price)) {
                            return (int) ($price / 10);
                        }
                    }
                } else {
                    if ($logger) {
                        $logger->warning('API returned non-array data');
                    }
                }
            } else {
                if ($logger) {
                    $logger->warning("API request failed with status: {$response->status()}");
                }
            }
        } catch (\Exception $e) {
            if ($logger) {
                $logger->warning("API method exception: {$e->getMessage()}");
            }
        }

        return null;
    }

    private static function tryWebApiMethod(string $productId, $logger = null): ?int
    {
        $endpoints = [
            "https://www.digikala.com/api/v1/product/{$productId}/",
            "https://api.digikala.com/v2/product/{$productId}/",
            "https://www.digikala.com/api/v2/product/{$productId}/",
        ];

        foreach ($endpoints as $endpoint) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => 'application/json',
                    'Accept-Language' => 'fa-IR,fa;q=0.9',
                    'Referer' => 'https://www.digikala.com/',
                ])->timeout(10)->get($endpoint);

                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data)) {
                        $paths = [
                            'data.product.default_variant.price.selling_price',
                            'data.product.price.selling_price',
                            'data.product.defaultVariant.price.selling_price',
                            'product.default_variant.price.selling_price',
                            'product.price.selling_price',
                        ];

                        foreach ($paths as $path) {
                            $price = self::getNestedValue($data, $path);
                            if ($price && is_numeric($price)) {
                                return (int) ($price / 10);
                            }
                        }
                    } else {
                        if ($logger) {
                            $logger->warning("Web API endpoint {$endpoint} returned non-array data");
                        }
                    }
                } else {
                    if ($logger) {
                        $logger->warning("Web API endpoint {$endpoint} failed with status: {$response->status()}");
                    }
                }
            } catch (\Exception $e) {
                if ($logger) {
                    $logger->warning("Web API endpoint {$endpoint} exception: {$e->getMessage()}");
                }

                // Continue to next endpoint
                continue;
            }
        }

        return null;
    }

    private static function tryHtmlScraping(string $productId, $logger = null): ?int
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'fa-IR,fa;q=0.9,en-US;q=0.8,en;q=0.7',
                'Referer' => 'https://www.digikala.com/',
            ])->timeout(15)->get("https://www.digikala.com/product/dkp-{$productId}/");

            if ($response->successful()) {
                $html = $response->body();

                // Method 1: Look for __NEXT_DATA__ script tag (Next.js)
                if (preg_match('/<script[^>]*id=["\']__NEXT_DATA__["\'][^>]*>(.+?)<\/script>/s', $html, $matches)) {
                    $jsonData = json_decode($matches[1], true);
                    if ($jsonData && is_array($jsonData)) {
                        // Try different paths in Next.js data structure
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

                // Method 2: Extract prices from HTML elements (main price and seller prices)
                $prices = self::extractPricesFromHtml($html, $logger);
                if (! empty($prices)) {
                    // Return the minimum price (lowest available price)
                    $minPrice = min($prices);
                    if ($logger) {
                        $logger->info('Found '.count($prices)." prices, returning minimum: {$minPrice}");
                    }

                    return $minPrice;
                }

                // Method 3: Look for price in various JSON patterns
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
                        // Validate price is reasonable (between 1000 and 100000000)
                        if ($price >= 1000 && $price <= 100000000) {
                            return $price;
                        }
                    }
                }

                // Method 4: Look for price in data attributes
                if (preg_match('/data-price=["\'](\d+)["\']/', $html, $matches)) {
                    $price = (int) $matches[1];
                    if ($price >= 1000 && $price <= 100000000) {
                        return $price;
                    }
                }
            } else {
                if ($logger) {
                    $logger->warning("HTML request failed with status: {$response->status()}");
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
     * Extract prices from HTML content (main price and seller prices)
     */
    private static function extractPricesFromHtml(string $html, $logger = null): array
    {
        $prices = [];

        // Extract main price from data-testid="price-no-discount"
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

        // Extract prices from seller section
        // Look for prices in seller list items (within sellerSection id or seller containers)
        // Pattern 1: Prices in seller list items with class styles_SellerListItemDesktop
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

        // Pattern 2: Look for prices in containers with min-w-[380px] (seller price containers)
        // This pattern matches: <div class="flex items-center justify-end min-w-[380px]...">...<span class="text-h4 ... text-neutral-800">PRICE</span>
        $alternativePattern = '/<div[^>]*min-w-\[380px\][^>]*>.*?<span[^>]*text-h4[^>]*text-neutral-800[^>]*>([^<]+)<\/span>/is';
        if (preg_match_all($alternativePattern, $html, $altMatches, PREG_SET_ORDER)) {
            foreach ($altMatches as $match) {
                $priceText = trim($match[1]);
                $price = self::parsePersianPrice($priceText);
                if ($price && $price >= 1000 && $price <= 100000000 && ! in_array($price, $prices)) {
                    $prices[] = $price;
                    if ($logger) {
                        $logger->info("Found alternative seller price: {$priceText} -> {$price}");
                    }
                }
            }
        }

        return array_unique($prices);
    }

    /**
     * Parse price text that may contain Persian digits and convert to integer
     */
    private static function parsePersianPrice(string $priceText): ?int
    {
        // Convert Persian digits to English digits
        $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $normalized = str_replace($persianDigits, $englishDigits, $priceText);

        // Remove commas and spaces
        $normalized = preg_replace('/[,\s]/', '', $normalized);

        // Extract only digits
        if (preg_match('/(\d+)/', $normalized, $matches)) {
            $price = (int) $matches[1];

            return $price;
        }

        return null;
    }

    /**
     * Convert Toman to Rial (1 Toman = 10 Rial)
     */
    private static function convertTomanToRial(int $priceInToman): int
    {
        return $priceInToman * 10;
    }

    /**
     * Get nested value from array using dot notation
     */
    private static function getNestedValue(array $array, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $array;

        foreach ($keys as $key) {
            if (! isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }
}
