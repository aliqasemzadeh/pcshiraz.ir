<?php

namespace Database\Seeders;

use App\Enums\ItemTypeEnum;
use App\Enums\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Models\User;
use App\Services\Shop\CategoryMenuService;
use App\Services\Shop\DemoMediaDownloader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    protected DemoMediaDownloader $media;

    public function run(): void
    {
        $this->media = app(DemoMediaDownloader::class);

        User::query()->firstOrCreate(
            ['mobile' => '09177886099'],
            [
                'email' => 'admin@pcshiraz.ir',
                'password' => 'password',
            ]
        );

        $categories = $this->seedCategories();
        // Skip brands and items as requested
        // $brands = $this->seedBrands();
        // $this->seedItems($categories, $brands);
        // $this->seedItemImages();

        app(CategoryMenuService::class)->forget();
    }

    /**
     * @return list<Category>
     */
    protected function seedCategories(): array
    {
        $titles = [
            ['موبایل', 'mobile'],
            ['نوت بوک', 'notebook'],
            ['موبایل شارژ', 'mobile-charge'],
            ['پاور بانک', 'power-bank'],
            ['سیستم آماده', 'pre-built-system'],
        ];

        $categories = [];
        $assetsDir = database_path('seeders/assets/categories');

        // Ensure directory exists
        if (!is_dir($assetsDir)) {
            mkdir($assetsDir, 0755, true);
        }

        foreach ($titles as $index => [$title, $slug]) {
            $category = Category::query()->updateOrCreate(
                [
                    'slug' => $slug,
                ],
                [
                    'title' => $title,
                    'seo_title' => $title,
                    'sort_order' => $index + 1,
                    'show_on_home' => true,
                ]
            );

            $this->createAndAttachSvgLogo($category, $assetsDir, $slug, $title);
            $categories[] = $category;
        }

        return $categories;
    }

    protected function createAndAttachSvgLogo(Category $category, string $assetsDir, string $slug, string $title): void
    {
        $svgPath = $assetsDir.DIRECTORY_SEPARATOR.$slug.'.svg';

        // Simple placeholder SVG with the first letter of the title
        $firstLetter = mb_substr($title, 0, 1);
        $svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
    <rect width="100" height="100" fill="#f3f4f6"/>
    <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="50" fill="#4b5563">$firstLetter</text>
</svg>
SVG;

        file_put_contents($svgPath, $svgContent);

        $this->media->attachFromPath($category, $svgPath, 'logo_image', $slug.'.svg');
    }

    /**
     * @return list<Brand>
     */
    protected function seedBrands(): array
    {
        // brand domain used for clearbit logo download: brand domain => title
        $titles = [
            ['Samsung', 'samsung.com'],
            ['Apple', 'apple.com'],
            ['Xiaomi', 'mi.com'],
            ['Huawei', 'huawei.com'],
            ['Nokia', 'nokia.com'],
            ['Honor', 'honor.com'],
            ['Realme', 'realme.com'],
            ['OPPO', 'oppo.com'],
            ['vivo', 'vivo.com'],
            ['OnePlus', 'oneplus.com'],
            ['Nothing', 'nothing.tech'],
            ['Google', 'google.com'],
            ['ASUS', 'asus.com'],
            ['Lenovo', 'lenovo.com'],
            ['HP', 'hp.com'],
            ['Dell', 'dell.com'],
            ['Acer', 'acer.com'],
            ['MSI', 'msi.com'],
            ['Microsoft', 'microsoft.com'],
            ['Sony', 'sony.com'],
            ['LG', 'lg.com'],
            ['JBL', 'jbl.com'],
            ['Anker', 'anker.com'],
            ['Baseus', 'baseus.com'],
            ['Ugreen', 'ugreen.com'],
            ['Belkin', 'belkin.com'],
            ['Logitech', 'logitech.com'],
            ['Razer', 'razer.com'],
            ['SteelSeries', 'steelseries.com'],
            ['Kingston', 'kingston.com'],
            ['Sandisk', 'sandisk.com'],
            ['Western Digital', 'westerndigital.com'],
            ['TP-Link', 'tp-link.com'],
            ['D-Link', 'dlink.com'],
            ['Xiaomi Accessories', 'mi.com'],
            ['Spigen', 'spigen.com'],
            ['Nillkin', 'nillkin.com'],
            ['Green Lion', 'greenlion.com'],
            ['Havit', 'havit.hk'],
            ['Tsco', 'tsco.ir'],
            ['Earldom', 'earldom.net'],
            ['Remax', 'remax.com'],
            ['Yesido', 'yesido.com'],
            ['Borofone', 'borofone.com'],
            ['Hoco', 'hoco.hk'],
            ['Katun', 'katun.com'],
            ['Nintendo', 'nintendo.com'],
            ['PlayStation', 'playstation.com'],
            ['Xbox', 'xbox.com'],
            ['Marshall', 'marshallheadphones.com'],
        ];

        $brands = [];

        foreach ($titles as $index => [$title, $logoDomain]) {
            $slug = Str::slug($title) ?: 'brand-'.($index + 1);

            $brand = Brand::query()->updateOrCreate(
                [
                    'slug' => $slug,
                ],
                [
                    'title' => $title,
                    'seo_title' => $title,
                    'sort_order' => $index + 1,
                ]
            );

            $logoUrl = 'https://logo.clearbit.com/'.$logoDomain.'?size=128';
            $attached = $this->media->attachFromUrl($brand, $logoUrl, 'logo_image', $slug.'.png');

            if (! $attached && $brand->getFirstMedia('logo_image') === null) {
                $placeholder = 'https://placehold.co/128x128/png?text='.urlencode(mb_substr($title, 0, 2));
                $this->media->attachFromUrl($brand, $placeholder, 'logo_image', $slug.'.png');
            }

            $brands[] = $brand;
        }

        return $brands;
    }

    /**
     * @param  list<Category>  $categories
     * @param  list<Brand>  $brands
     */
    protected function seedItems(array $categories, array $brands): void
    {
        if ($categories === [] || $brands === []) {
            return;
        }

        $brandsBySlug = collect($brands)->keyBy('slug');
        $categoryBrandMap = $this->categoryBrandSlugs();

        $existingCount = Item::query()->count();

        if ($existingCount >= 500) {
            return;
        }

        $colors = [
            ['مشکی', '#111827'],
            ['سفید', '#F9FAFB'],
            ['نقره‌ای', '#9CA3AF'],
            ['آبی', '#2563EB'],
            ['قرمز', '#DC2626'],
            ['سبز', '#16A34A'],
        ];

        $target = 550;
        $toCreate = $target - $existingCount;
        $now = now();
        $items = [];

        foreach ($categories as $category) {
            $brandSlugs = $categoryBrandMap[$category->slug] ?? [];
            $relatedBrands = [];

            foreach ($brandSlugs as $brandSlug) {
                if ($brandsBySlug->has($brandSlug)) {
                    $relatedBrands[] = $brandsBySlug->get($brandSlug);
                }
            }

            if ($relatedBrands === []) {
                $relatedBrands = $this->brandsForCategory($brands, array_search($category, $categories, true) ?: 0, 10);
            }

            foreach ($relatedBrands as $brandOffset => $brand) {
                $slug = sprintf('demo-%d-%d', $category->id, $brand->id);

                if (Item::query()->where('slug', $slug)->exists()) {
                    continue;
                }

                $title = sprintf('%s %s مدل %s', $brand->title, $category->title, chr(65 + ($brandOffset % 26)));
                [$colorName, $colorCode] = $colors[$brandOffset % count($colors)];

                $items[] = [
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'item_type' => ItemTypeEnum::Product->value,
                    'group_id' => null,
                    'is_main' => true,
                    'is_active' => true,
                    'is_purchasable' => true,
                    'views_count' => 0,
                    'title' => $title,
                    'description' => 'کالای دمو برای نمایش مگامنو دسته‌ها و برندها',
                    'color_code' => $colorCode,
                    'color_name' => $colorName,
                    'weight' => null,
                    'length' => null,
                    'width' => null,
                    'height' => null,
                    'seo_title' => $title,
                    'slug' => $slug,
                    'meta_description' => null,
                    'meta' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $remaining = max(0, $toCreate - count($items));

        for ($i = 0; $i < $remaining; $i++) {
            $category = $categories[array_rand($categories)];
            $brand = $brands[array_rand($brands)];
            $slug = sprintf('demo-extra-%d', $existingCount + count($items) + $i + 1);
            $title = sprintf('%s %s #%d', $brand->title, $category->title, $i + 1);
            [$colorName, $colorCode] = $colors[$i % count($colors)];

            $items[] = [
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'item_type' => ItemTypeEnum::Product->value,
                'group_id' => null,
                'is_main' => true,
                'is_active' => true,
                'is_purchasable' => true,
                'views_count' => 0,
                'title' => $title,
                'description' => 'کالای دمو برای نمایش مگامنو دسته‌ها و برندها',
                'color_code' => $colorCode,
                'color_name' => $colorName,
                'weight' => null,
                'length' => null,
                'width' => null,
                'height' => null,
                'seo_title' => $title,
                'slug' => $slug,
                'meta_description' => null,
                'meta' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($items, 100) as $chunk) {
            Item::query()->insert($chunk);
        }

        $createdItems = Item::query()
            ->whereDoesntHave('itemPrices')
            ->get(['id']);

        $prices = [];

        foreach ($createdItems as $item) {
            $price = random_int(1_500_000, 85_000_000);

            $prices[] = [
                'item_id' => $item->id,
                'price_type' => PriceTypeEnum::Cash->value,
                'price' => $price,
                'sale_price' => $price,
                'meta' => null,
                'sales_cap' => null,
                'total_sales_count' => 0,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($prices, 100) as $chunk) {
            ItemPrice::query()->insert($chunk);
        }
    }

    protected function seedItemImages(): void
    {
        $items = Item::query()
            ->whereDoesntHave('media', fn ($q) => $q->where('collection_name', 'product_image'))
            ->limit(120)
            ->get();

        foreach ($items as $index => $item) {
            $seed = ($item->id % 70) + 1;
            $url = "https://picsum.photos/seed/pcshiraz-{$seed}/600/600.webp";

            $this->media->attachFromUrl($item, $url, 'product_image', "product-{$item->id}.webp");

            if (($index + 1) % 20 === 0) {
                usleep(100_000);
            }
        }
    }

    /**
     * Map each category slug to related brand slugs (mega-menu subsets).
     *
     * @return array<string, list<string>>
     */
    protected function categoryBrandSlugs(): array
    {
        return [
            'mobile' => ['samsung', 'apple', 'xiaomi', 'huawei', 'nokia', 'honor', 'realme', 'oppo', 'vivo', 'oneplus', 'nothing', 'google'],
            'mobile-parts' => ['samsung', 'apple', 'xiaomi', 'huawei', 'nokia', 'honor'],
            'laptop' => ['asus', 'lenovo', 'hp', 'dell', 'acer', 'msi', 'apple', 'microsoft', 'huawei'],
            'tablet' => ['samsung', 'apple', 'xiaomi', 'huawei', 'lenovo', 'microsoft'],
            'phone-charger' => ['anker', 'baseus', 'ugreen', 'hoco', 'remax', 'earldom', 'yesido', 'green-lion'],
            'case-cover' => ['spigen', 'nillkin', 'green-lion', 'tsco', 'hoco', 'yesido'],
            'headphones' => ['jbl', 'sony', 'apple', 'samsung', 'anker', 'marshall', 'havit', 'remax'],
            'smartwatch' => ['apple', 'samsung', 'xiaomi', 'huawei', 'honor', 'nothing'],
            'cable-adapter' => ['baseus', 'ugreen', 'anker', 'belkin', 'hoco', 'remax', 'earldom', 'borofone'],
            'hub' => ['ugreen', 'baseus', 'anker', 'belkin', 'tp-link'],
            'speaker' => ['jbl', 'sony', 'marshall', 'havit', 'xiaomi', 'anker'],
            'storage' => ['kingston', 'sandisk', 'western-digital', 'samsung', 'xiaomi'],
            'game-console' => ['sony', 'microsoft', 'nintendo', 'playstation', 'xbox'],
            'power-bank' => ['anker', 'baseus', 'xiaomi', 'green-lion', 'hoco', 'remax', 'tsco'],
            'phone-holder' => ['baseus', 'yesido', 'hoco', 'earldom', 'green-lion', 'tsco'],
            'keyboard-mouse' => ['logitech', 'razer', 'steelseries', 'microsoft', 'havit', 'tsco'],
            'monitor' => ['lg', 'samsung', 'msi', 'asus', 'dell'],
            'power-protector' => ['tp-link', 'tsco', 'green-lion', 'ugreen'],
            'home-gadget' => ['xiaomi', 'huawei', 'samsung', 'anker', 'baseus'],
            'home-appliance' => ['samsung', 'lg', 'xiaomi', 'huawei'],
            'glass-protector' => ['spigen', 'nillkin', 'green-lion', 'hoco', 'tsco'],
            'computer-gear' => ['logitech', 'razer', 'steelseries', 'kingston', 'asus', 'msi'],
            'modem-network' => ['tp-link', 'd-link', 'xiaomi', 'huawei', 'asus'],
            'smart-home' => ['xiaomi', 'google', 'apple', 'tp-link', 'huawei'],
            'network-camera' => ['tp-link', 'xiaomi', 'd-link'],
            'computer' => ['asus', 'lenovo', 'hp', 'dell', 'msi', 'acer'],
            'android-box' => ['xiaomi', 'google', 'asus'],
            'mobile-accessories' => ['baseus', 'hoco', 'remax', 'yesido', 'earldom', 'borofone', 'green-lion', 'tsco', 'spigen'],
            'smart-tag' => ['apple', 'samsung', 'xiaomi', 'baseus'],
            'other' => ['samsung', 'xiaomi', 'anker', 'baseus', 'tsco', 'hoco'],
        ];
    }

    /**
     * @param  list<Brand>  $brands
     * @return list<Brand>
     */
    protected function brandsForCategory(array $brands, int $categoryIndex, int $count): array
    {
        $total = count($brands);
        $selected = [];

        for ($i = 0; $i < $count; $i++) {
            $selected[] = $brands[($categoryIndex * 3 + $i) % $total];
        }

        return $selected;
    }
}
