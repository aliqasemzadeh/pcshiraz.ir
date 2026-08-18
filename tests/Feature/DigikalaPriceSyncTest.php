<?php

namespace Tests\Feature;

use App\Enums\PriceTypeEnum;
use App\Enums\PriceUnitEnum;
use App\Jobs\UpdatePriceJob;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Services\Sale\DigikalaPriceSyncService;
use App\Settings\GeneralSettings;
use App\Support\DigikalaPriceFetcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DigikalaPriceSyncTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function fetch_variants_returns_color_specific_prices(): void
    {
        Http::fake([
            'api.digikala.com/v1/product/20109389/*' => Http::response($this->sampleProductPayload()),
        ]);

        $variants = DigikalaPriceFetcher::fetchVariants(
            'https://www.digikala.com/product/dkp-20109389/sample-product/'
        );

        $this->assertCount(2, $variants);
        $this->assertSame(111, $variants[0]['variant_id']);
        $this->assertSame('مشکی', $variants[0]['color_title']);
        $this->assertSame(12500000, $variants[0]['price_toman']);
        $this->assertSame(222, $variants[1]['variant_id']);
        $this->assertSame('آبی', $variants[1]['color_title']);
        $this->assertSame(12800000, $variants[1]['price_toman']);
    }

    #[Test]
    public function fetch_price_returns_variant_specific_price(): void
    {
        Http::fake([
            'api.digikala.com/v1/product/20109389/*' => Http::response($this->sampleProductPayload()),
        ]);

        $price = DigikalaPriceFetcher::fetchPrice(
            'https://www.digikala.com/product/dkp-20109389/sample-product/',
            222,
        );

        $this->assertSame(12800000, $price);
    }

    #[Test]
    public function suggest_variant_id_matches_item_color_name(): void
    {
        $variants = [
            ['variant_id' => 111, 'color_id' => 1, 'color_title' => 'مشکی', 'price_toman' => 100, 'is_available' => true],
            ['variant_id' => 222, 'color_id' => 2, 'color_title' => 'آبی', 'price_toman' => 200, 'is_available' => true],
        ];

        $this->assertSame(111, DigikalaPriceFetcher::suggestVariantId($variants, 'مشکی'));
    }

    #[Test]
    public function sync_item_creates_new_cash_price_when_digikala_price_changes(): void
    {
        Http::fake([
            'api.digikala.com/v1/product/20109389/*' => Http::response($this->sampleProductPayload()),
        ]);

        $item = $this->createItemWithDigikalaConfig(variantId: 111);

        ItemPrice::query()->create([
            'item_id' => $item->id,
            'price_type' => PriceTypeEnum::Cash,
            'price' => 10000000,
            'sale_price' => 10000000,
            'is_active' => true,
        ]);

        $result = app(DigikalaPriceSyncService::class)->syncItem($item->fresh());

        $this->assertTrue($result->success);
        $this->assertSame('success', $result->status);
        $this->assertSame(12500000, $result->price);

        $activePrice = ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('price_type', PriceTypeEnum::Cash)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($activePrice);
        $this->assertSame('12500000.0000', (string) $activePrice->sale_price);
        $this->assertSame('success', $item->fresh()->digikala_last_sync_status);

        $activeInstallment = ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('price_type', PriceTypeEnum::Installment)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($activeInstallment);
        $this->assertSame('12500000.0000', (string) $activeInstallment->sale_price);
    }

    #[Test]
    public function sync_item_stores_digikala_toman_even_when_display_unit_is_rial(): void
    {
        $settings = app(GeneralSettings::class);
        $settings->price_unit = PriceUnitEnum::Rial->value;
        $settings->save();

        Http::fake([
            'api.digikala.com/v1/product/20109389/*' => Http::response($this->sampleProductPayload()),
        ]);

        $item = $this->createItemWithDigikalaConfig(variantId: 111);

        $result = app(DigikalaPriceSyncService::class)->syncItem($item->fresh());

        $this->assertTrue($result->success);
        $this->assertSame(12500000, $result->price);

        $activePrice = ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('price_type', PriceTypeEnum::Cash)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($activePrice);
        $this->assertSame('12500000.0000', (string) $activePrice->sale_price);
        $this->assertSame('12500000.0000', (string) $activePrice->price);
    }

    #[Test]
    public function sync_item_converts_api_rial_selling_price_to_toman(): void
    {
        Http::fake([
            'api.digikala.com/v1/product/99999999/*' => Http::response([
                'data' => [
                    'product' => [
                        'id' => 99999999,
                        'variants' => [
                            [
                                'id' => 111,
                                'status' => 'marketable',
                                'color' => ['id' => 1, 'title' => 'مشکی'],
                                'price' => ['selling_price' => 335130000],
                            ],
                        ],
                        'default_variant' => [
                            'id' => 111,
                            'price' => ['selling_price' => 335130000],
                        ],
                    ],
                ],
            ]),
        ]);

        $item = $this->createItemWithDigikalaConfig(variantId: 111);
        $item->update([
            'digikala_url' => 'https://www.digikala.com/product/dkp-99999999/sample-product/',
            'digikala_product_id' => '99999999',
        ]);

        $result = app(DigikalaPriceSyncService::class)->syncItem($item->fresh());

        $this->assertTrue($result->success);
        $this->assertSame(33513000, $result->price);

        $activePrice = ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('price_type', PriceTypeEnum::Installment)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($activePrice);
        $this->assertSame('33513000.0000', (string) $activePrice->sale_price);
    }

    #[Test]
    public function sync_item_skips_when_cash_and_installment_are_unchanged(): void
    {
        Http::fake([
            'api.digikala.com/v1/product/20109389/*' => Http::response($this->sampleProductPayload()),
        ]);

        $item = $this->createItemWithDigikalaConfig(variantId: 111);

        ItemPrice::query()->create([
            'item_id' => $item->id,
            'price_type' => PriceTypeEnum::Cash,
            'price' => 12500000,
            'sale_price' => 12500000,
            'is_active' => true,
        ]);

        ItemPrice::query()->create([
            'item_id' => $item->id,
            'price_type' => PriceTypeEnum::Installment,
            'price' => 12500000,
            'sale_price' => 12500000,
            'is_active' => true,
        ]);

        $beforeCount = ItemPrice::query()->where('item_id', $item->id)->count();

        $result = app(DigikalaPriceSyncService::class)->syncItem($item->fresh());

        $this->assertTrue($result->success);
        $this->assertSame('unchanged', $result->status);
        $this->assertSame($beforeCount, ItemPrice::query()->where('item_id', $item->id)->count());
    }

    #[Test]
    public function sync_item_creates_installment_when_cash_already_matches(): void
    {
        Http::fake([
            'api.digikala.com/v1/product/20109389/*' => Http::response($this->sampleProductPayload()),
        ]);

        $item = $this->createItemWithDigikalaConfig(variantId: 111);

        ItemPrice::query()->create([
            'item_id' => $item->id,
            'price_type' => PriceTypeEnum::Cash,
            'price' => 12500000,
            'sale_price' => 12500000,
            'is_active' => true,
        ]);

        ItemPrice::query()->create([
            'item_id' => $item->id,
            'price_type' => PriceTypeEnum::Installment,
            'price' => 10000000,
            'sale_price' => 10000000,
            'is_active' => true,
        ]);

        $result = app(DigikalaPriceSyncService::class)->syncItem($item->fresh());

        $this->assertTrue($result->success);
        $this->assertSame('success', $result->status);

        $activeCash = ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('price_type', PriceTypeEnum::Cash)
            ->where('is_active', true)
            ->first();

        $activeInstallment = ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('price_type', PriceTypeEnum::Installment)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($activeCash);
        $this->assertSame('12500000.0000', (string) $activeCash->sale_price);
        $this->assertNotNull($activeInstallment);
        $this->assertSame('12500000.0000', (string) $activeInstallment->sale_price);
        $this->assertSame(1, ItemPrice::query()->where('item_id', $item->id)->where('price_type', PriceTypeEnum::Cash)->count());
        $this->assertSame(2, ItemPrice::query()->where('item_id', $item->id)->where('price_type', PriceTypeEnum::Installment)->count());
    }

    #[Test]
    public function command_dispatches_jobs_only_for_auto_sync_items(): void
    {
        Queue::fake();

        $syncItem = $this->createItemWithDigikalaConfig(autoSync: true);
        $this->createItemWithDigikalaConfig(autoSync: false);

        $this->artisan('prices:sync-digikala')
            ->assertSuccessful();

        Queue::assertPushed(UpdatePriceJob::class, 1);
        Queue::assertPushed(UpdatePriceJob::class, fn (UpdatePriceJob $job): bool => $job->item->is($syncItem));
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleProductPayload(): array
    {
        return [
            'data' => [
                'product' => [
                    'id' => 20109389,
                    'variants' => [
                        [
                            'id' => 111,
                            'status' => 'marketable',
                            'color' => ['id' => 1, 'title' => 'مشکی'],
                            'price' => ['selling_price' => 125000000],
                        ],
                        [
                            'id' => 222,
                            'status' => 'marketable',
                            'color' => ['id' => 2, 'title' => 'آبی'],
                            'price' => ['selling_price' => 128000000],
                        ],
                    ],
                    'default_variant' => [
                        'id' => 111,
                        'price' => ['selling_price' => 125000000],
                    ],
                ],
            ],
        ];
    }

    private function createItemWithDigikalaConfig(bool $autoSync = true, ?int $variantId = 111): Item
    {
        $brand = Brand::query()->create([
            'title' => 'Brand '.uniqid(),
            'slug' => 'brand-'.uniqid(),
            'is_active' => true,
        ]);

        $category = Category::query()->create([
            'title' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
            'is_active' => true,
        ]);

        return Item::query()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'title' => 'Item '.uniqid(),
            'slug' => 'item-'.uniqid(),
            'color_name' => 'مشکی',
            'is_active' => true,
            'is_purchasable' => true,
            'is_main' => true,
            'stock' => 5,
            'digikala_url' => 'https://www.digikala.com/product/dkp-20109389/sample-product/',
            'digikala_product_id' => '20109389',
            'digikala_variant_id' => $variantId,
            'digikala_auto_sync' => $autoSync,
        ]);
    }
}
