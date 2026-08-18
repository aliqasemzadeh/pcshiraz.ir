<?php

namespace Tests\Feature;

use App\Enums\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ShopItemQuickManageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_cannot_access_quick_manage_component(): void
    {
        $item = $this->createItem();

        Livewire::test('shop.item.quick-manage', ['itemId' => $item->id])
            ->assertForbidden();
    }

    #[Test]
    public function user_without_permission_cannot_access_quick_manage_component(): void
    {
        $user = User::factory()->create();
        $item = $this->createItem();

        Livewire::actingAs($user)
            ->test('shop.item.quick-manage', ['itemId' => $item->id])
            ->assertForbidden();
    }

    #[Test]
    public function authorized_user_can_increase_and_decrease_stock(): void
    {
        $user = $this->createAuthorizedUser();
        $item = $this->createItem(stock: 5);

        Livewire::actingAs($user)
            ->test('shop.item.quick-manage', ['itemId' => $item->id])
            ->call('adjustStock', 1)
            ->assertDispatched('shop.item.updated');

        $this->assertSame(6, $item->fresh()->stock);
        $this->assertTrue($item->fresh()->is_purchasable);

        Livewire::actingAs($user)
            ->test('shop.item.quick-manage', ['itemId' => $item->id])
            ->call('adjustStock', -6);

        $this->assertSame(0, $item->fresh()->stock);
        $this->assertFalse($item->fresh()->is_purchasable);
    }

    #[Test]
    public function authorized_user_can_save_cash_price(): void
    {
        $user = $this->createAuthorizedUser();
        $item = $this->createItem(stock: 3);

        Livewire::actingAs($user)
            ->test('shop.item.quick-manage', ['itemId' => $item->id])
            ->set('form.price', '15000000')
            ->set('form.sale_price', '14500000')
            ->call('savePrice')
            ->assertDispatched('shop.item.updated');

        $activePrice = ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('price_type', PriceTypeEnum::Cash)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($activePrice);
        $this->assertSame('14500000.0000', (string) $activePrice->sale_price);
        $this->assertSame('15000000.0000', (string) $activePrice->price);
        $this->assertSame(3, $item->fresh()->stock);

        $activeInstallment = ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('price_type', PriceTypeEnum::Installment)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($activeInstallment);
        $this->assertSame('14500000.0000', (string) $activeInstallment->sale_price);
        $this->assertSame('15000000.0000', (string) $activeInstallment->price);
    }

    #[Test]
    public function authorized_user_can_save_masked_cash_price(): void
    {
        $user = $this->createAuthorizedUser();
        $item = $this->createItem(stock: 2);

        Livewire::actingAs($user)
            ->test('shop.item.quick-manage', ['itemId' => $item->id])
            ->set('form.price', '15,000,000')
            ->set('form.sale_price', '14,500,000')
            ->call('savePrice')
            ->assertHasNoErrors()
            ->assertDispatched('shop.item.updated');

        $activePrice = ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('price_type', PriceTypeEnum::Cash)
            ->where('is_active', true)
            ->first();

        $this->assertNotNull($activePrice);
        $this->assertSame('14500000.0000', (string) $activePrice->sale_price);
        $this->assertSame('15000000.0000', (string) $activePrice->price);
    }

    #[Test]
    public function saving_installment_price_does_not_change_cash_price(): void
    {
        $user = $this->createAuthorizedUser();
        $item = $this->createItem();

        ItemPrice::query()->create([
            'item_id' => $item->id,
            'price_type' => PriceTypeEnum::Cash,
            'price' => 10000000,
            'sale_price' => 10000000,
            'is_active' => true,
        ]);

        Livewire::actingAs($user)
            ->test('shop.item.quick-manage', ['itemId' => $item->id])
            ->call('selectType', PriceTypeEnum::Installment->value)
            ->set('form.price', '12000000')
            ->set('form.sale_price', '12000000')
            ->call('savePrice')
            ->assertDispatched('shop.item.updated');

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
        $this->assertSame('10000000.0000', (string) $activeCash->sale_price);
        $this->assertNotNull($activeInstallment);
        $this->assertSame('12000000.0000', (string) $activeInstallment->sale_price);
    }

    #[Test]
    public function authorized_user_can_sync_digikala_price(): void
    {
        Http::fake([
            'api.digikala.com/v1/product/20109389/*' => Http::response($this->sampleProductPayload()),
        ]);

        $user = $this->createAuthorizedUser();
        $item = $this->createItemWithDigikalaConfig();

        Livewire::actingAs($user)
            ->test('shop.item.quick-manage', ['itemId' => $item->id])
            ->call('syncDigikalaNow')
            ->assertDispatched('shop.item.updated');

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
    public function shop_item_page_shows_quick_manage_for_authorized_user(): void
    {
        $user = $this->createAuthorizedUser();
        $item = $this->createItem();

        $this->actingAs($user)
            ->get(route('shop.item', $item->shopRoute()))
            ->assertOk()
            ->assertSee(__('app.item_quick_manage'));
    }

    private function createAuthorizedUser(): User
    {
        Permission::findOrCreate('sale.item_edit', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo('sale.item_edit');

        return $user;
    }

    private function createItem(int $stock = 5): Item
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
            'is_active' => true,
            'is_purchasable' => $stock > 0,
            'is_main' => true,
            'stock' => $stock,
        ]);
    }

    private function createItemWithDigikalaConfig(): Item
    {
        $item = $this->createItem();

        $item->update([
            'digikala_url' => 'https://www.digikala.com/product/dkp-20109389/sample-product/',
            'digikala_product_id' => '20109389',
            'digikala_variant_id' => 111,
            'digikala_auto_sync' => true,
        ]);

        return $item->fresh();
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
                            'price' => ['selling_price' => 12500000],
                        ],
                    ],
                    'default_variant' => [
                        'id' => 111,
                        'price' => ['selling_price' => 12500000],
                    ],
                ],
            ],
        ];
    }
}
