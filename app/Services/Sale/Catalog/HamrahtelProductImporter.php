<?php

namespace App\Services\Sale\Catalog;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class HamrahtelProductImporter
{
    private const GRAPHQL_ENDPOINT = 'https://core-api.hamrahtel.com/graphql/';

    /**
     * @return array{
     *     title: string,
     *     slug: string,
     *     description: ?string,
     *     seo_title: ?string,
     *     meta_description: ?string,
     *     image_url: ?string,
     *     color_name: ?string,
     *     color_code: ?string
     * }
     */
    public function import(string $url): array
    {
        $slug = $this->extractSlug($url);

        if ($slug === null) {
            throw new RuntimeException(__('general.hamrahtel_invalid_url'));
        }

        $product = $this->fetchProduct($slug);

        if ($product === null) {
            throw new RuntimeException(__('general.hamrahtel_import_failed'));
        }

        [$colorName, $colorCode] = $this->extractColor($product['attributes'] ?? []);

        return [
            'title' => (string) ($product['name'] ?? ''),
            'slug' => (string) ($product['slug'] ?? $slug),
            'description' => $this->descriptionToText($product['description'] ?? null),
            'seo_title' => $this->nullableString($product['seoTitle'] ?? null),
            'meta_description' => $this->nullableString($product['seoDescription'] ?? null),
            'image_url' => $this->resolveImageUrl($product),
            'color_name' => $colorName,
            'color_code' => $colorCode,
        ];
    }

    public function extractSlug(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);

        if ($parts === false) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');

        if (! in_array($host, ['hamrahtel.com', 'www.hamrahtel.com'], true)) {
            return null;
        }

        $path = trim($parts['path'] ?? '', '/');

        if (! str_starts_with($path, 'products/')) {
            return null;
        }

        $slug = Str::after($path, 'products/');
        $slug = Str::before($slug, '/');

        return $slug !== '' ? $slug : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function fetchProduct(string $slug): ?array
    {
        $query = <<<'GRAPHQL'
            query PublicProduct($slug: String!) {
                publicProduct(slug: $slug) {
                    id
                    name
                    slug
                    description
                    seoTitle
                    seoDescription
                    thumbnail { url alt }
                    media { url alt }
                    attributes {
                        attribute { name slug }
                        values { name }
                    }
                }
            }
            GRAPHQL;

        try {
            $response = Http::timeout(30)
                ->connectTimeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Origin' => 'https://hamrahtel.com',
                    'Referer' => 'https://hamrahtel.com/',
                    'User-Agent' => 'PCShiraz-HamrahtelImporter/1.0',
                ])
                ->post(self::GRAPHQL_ENDPOINT, [
                    'query' => $query,
                    'variables' => ['slug' => $slug],
                ]);
        } catch (ConnectionException|RequestException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $product = $response->json('data.publicProduct');

        return is_array($product) ? $product : null;
    }

    protected function descriptionToText(mixed $description): ?string
    {
        if (! is_string($description) || $description === '') {
            return null;
        }

        $decoded = json_decode($description, true);

        if (! is_array($decoded) || ! isset($decoded['blocks']) || ! is_array($decoded['blocks'])) {
            return strip_tags($description);
        }

        $lines = [];

        foreach ($decoded['blocks'] as $block) {
            if (! is_array($block)) {
                continue;
            }

            $text = $block['data']['text'] ?? null;

            if (! is_string($text) || trim($text) === '') {
                continue;
            }

            $lines[] = trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $text))));
        }

        $result = trim(implode("\n", array_filter($lines)));

        return $result !== '' ? $result : null;
    }

    /**
     * @param  list<array<string, mixed>>  $attributes
     * @return array{0: ?string, 1: ?string}
     */
    protected function extractColor(array $attributes): array
    {
        foreach ($attributes as $row) {
            if (! is_array($row)) {
                continue;
            }

            $attribute = $row['attribute'] ?? [];
            $name = strtolower((string) ($attribute['name'] ?? ''));
            $slug = strtolower((string) ($attribute['slug'] ?? ''));

            if (! str_contains($name, 'color') && ! str_contains($name, 'رنگ') && ! str_contains($slug, 'color')) {
                continue;
            }

            $values = $row['values'] ?? [];
            $first = is_array($values) ? ($values[0]['name'] ?? null) : null;
            $colorName = is_string($first) && $first !== '' ? $first : null;

            return [$colorName, null];
        }

        return [null, null];
    }

    /**
     * @param  array<string, mixed>  $product
     */
    protected function resolveImageUrl(array $product): ?string
    {
        $thumbnail = $product['thumbnail']['url'] ?? null;

        if (is_string($thumbnail) && $thumbnail !== '') {
            return $thumbnail;
        }

        $media = $product['media'] ?? [];

        if (is_array($media) && isset($media[0]['url']) && is_string($media[0]['url']) && $media[0]['url'] !== '') {
            return $media[0]['url'];
        }

        return null;
    }

    protected function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
