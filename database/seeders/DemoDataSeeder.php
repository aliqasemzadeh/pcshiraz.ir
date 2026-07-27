<?php

namespace Database\Seeders;

use App\Enums\ItemTypeEnum;
use App\Enums\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Domain;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Models\User;
use App\Services\Shop\CategoryMenuService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['mobile' => '09120000000'],
            [
                'email' => 'demo@pcshiraz.ir',
                'password' => 'password',
            ]
        );

        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';

        $domain = Domain::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'domain' => $host,
            ],
            [
                'title' => 'پی سی شیراز',
                'description' => 'فروشگاه دمو قطعات کامپیوتر',
            ]
        );

        $categories = $this->seedCategories($domain);
        $brands = $this->seedBrands($domain);
        $this->seedItems($domain, $categories, $brands);

        app(CategoryMenuService::class)->forget($domain);
    }

    /**
     * @return list<Category>
     */
    protected function seedCategories(Domain $domain): array
    {
        $titles = [
            ['پردازنده', 'cpu'],
            ['مادربرد', 'motherboard'],
            ['کارت گرافیک', 'graphics-card'],
            ['حافظه رم', 'ram'],
            ['اس اس دی', 'ssd'],
            ['هارد دیسک', 'hdd'],
            ['منبع تغذیه', 'power-supply'],
            ['کیس', 'case'],
            ['خنک‌کننده پردازنده', 'cpu-cooler'],
            ['فن کیس', 'case-fan'],
            ['مانیتور', 'monitor'],
            ['کیبورد', 'keyboard'],
            ['ماوس', 'mouse'],
            ['هدست گیمینگ', 'gaming-headset'],
            ['اسپیکر', 'speaker'],
            ['وب‌کم', 'webcam'],
            ['لپ‌تاپ', 'laptop'],
            ['مینی پی سی', 'mini-pc'],
            ['نوت‌بوک گیمینگ', 'gaming-notebook'],
            ['کارت صدا', 'sound-card'],
            ['کارت شبکه', 'network-card'],
            ['مودم و روتر', 'modem-router'],
            ['سوییچ شبکه', 'network-switch'],
            ['کابل شبکه', 'network-cable'],
            ['کابل تصویر', 'display-cable'],
            ['هاب USB', 'usb-hub'],
            ['درایو نوری', 'optical-drive'],
            ['پرینتر', 'printer'],
            ['اسکنر', 'scanner'],
            ['تبلت گرافیکی', 'drawing-tablet'],
            ['صندلی گیمینگ', 'gaming-chair'],
            ['میز کامپیوتر', 'desk'],
            ['پد ماوس', 'mouse-pad'],
            ['کیف لپ‌تاپ', 'laptop-bag'],
            ['باتری لپ‌تاپ', 'laptop-battery'],
            ['شارژر لپ‌تاپ', 'laptop-charger'],
            ['نرم‌افزار و لایسنس', 'software-license'],
            ['کنسول بازی', 'game-console'],
            ['دسته بازی', 'gamepad'],
            ['لوازم جانبی استریم', 'streaming-gear'],
        ];

        $categories = [];

        foreach ($titles as $index => [$title, $slug]) {
            $categories[] = Category::query()->updateOrCreate(
                [
                    'domain_id' => $domain->id,
                    'slug' => $slug,
                ],
                [
                    'title' => $title,
                    'seo_title' => $title,
                    'sort_order' => $index + 1,
                ]
            );
        }

        return $categories;
    }

    /**
     * @return list<Brand>
     */
    protected function seedBrands(Domain $domain): array
    {
        $titles = [
            'Intel',
            'AMD',
            'NVIDIA',
            'ASUS',
            'MSI',
            'Gigabyte',
            'ASRock',
            'Corsair',
            'G.Skill',
            'Kingston',
            'Samsung',
            'Western Digital',
            'Seagate',
            'Crucial',
            'TeamGroup',
            'Patriot',
            'Cooler Master',
            'DeepCool',
            'NZXT',
            'Lian Li',
            'Fractal Design',
            'be quiet!',
            'Thermaltake',
            'Seasonic',
            'EVGA',
            'Zotac',
            'Palit',
            'Sapphire',
            'PowerColor',
            'XFX',
            'Logitech',
            'Razer',
            'SteelSeries',
            'HyperX',
            'BenQ',
            'LG',
            'Dell',
            'HP',
            'Acer',
            'Lenovo',
            'Apple',
            'TP-Link',
            'D-Link',
            'Mikrotik',
            'Epson',
            'Canon',
            'Brother',
            'Wacom',
            'Sony',
            'Microsoft',
        ];

        $brands = [];

        foreach ($titles as $index => $title) {
            $brands[] = Brand::query()->updateOrCreate(
                [
                    'domain_id' => $domain->id,
                    'slug' => Str::slug($title) ?: 'brand-'.($index + 1),
                ],
                [
                    'title' => $title,
                    'seo_title' => $title,
                    'sort_order' => $index + 1,
                ]
            );
        }

        return $brands;
    }

    /**
     * @param  list<Category>  $categories
     * @param  list<Brand>  $brands
     */
    protected function seedItems(Domain $domain, array $categories, array $brands): void
    {
        if ($categories === [] || $brands === []) {
            return;
        }

        $existingCount = Item::query()->where('domain_id', $domain->id)->count();

        if ($existingCount >= 500) {
            return;
        }

        $target = 550;
        $toCreate = $target - $existingCount;
        $now = now();
        $items = [];
        $prices = [];

        // Ensure every category gets a rich set of brands via dedicated items first.
        foreach ($categories as $categoryIndex => $category) {
            $brandSlice = $this->brandsForCategory($brands, $categoryIndex, 12);

            foreach ($brandSlice as $brandOffset => $brand) {
                $slug = sprintf('demo-%d-%d-%d', $domain->id, $category->id, $brand->id);

                if (Item::query()->where('slug', $slug)->exists()) {
                    continue;
                }

                $title = sprintf('%s %s مدل %s', $brand->title, $category->title, chr(65 + ($brandOffset % 26)));

                $items[] = [
                    'domain_id' => $domain->id,
                    'brand_id' => $brand->id,
                    'category_id' => $category->id,
                    'item_type' => ItemTypeEnum::Product->value,
                    'group_id' => null,
                    'is_main' => true,
                    'title' => $title,
                    'description' => 'کالای دمو برای نمایش مگامنو دسته‌ها و برندها',
                    'color_code' => null,
                    'color_name' => null,
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
            $slug = sprintf('demo-extra-%d-%d', $domain->id, $existingCount + count($items) + $i + 1);
            $title = sprintf('%s %s #%d', $brand->title, $category->title, $i + 1);

            $items[] = [
                'domain_id' => $domain->id,
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'item_type' => ItemTypeEnum::Product->value,
                'group_id' => null,
                'is_main' => true,
                'title' => $title,
                'description' => 'کالای دمو برای نمایش مگامنو دسته‌ها و برندها',
                'color_code' => null,
                'color_name' => null,
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
            ->where('domain_id', $domain->id)
            ->whereDoesntHave('itemPrices')
            ->get(['id']);

        foreach ($createdItems as $item) {
            $price = random_int(1_500_000, 85_000_000) / 1;

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
