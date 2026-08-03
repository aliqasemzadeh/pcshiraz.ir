<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Enums\PriceTypeEnum;
use App\Models\Brand;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\InstallmentPlan;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Models\Organization;
use App\Models\User;
use App\Services\Sale\CartService;
use App\Services\Sale\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartCheckoutSaleTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function add_item_uses_cart_cash_sale_type_price(): void
    {
        [$user, $item] = $this->createUserWithPricedItem(
            cashPrice: '1000000',
            installmentPrice: '1200000',
        );

        $cartItem = app(CartService::class)->addItem($user, $item, 2);

        $this->assertSame(PriceTypeEnum::Cash, $cartItem->cart->sale_type);
        $this->assertSame('1000000.0000', (string) $cartItem->unit_price);
        $this->assertSame(2, $cartItem->quantity);
    }

    #[Test]
    public function set_sale_type_reprices_items_and_removes_unavailable(): void
    {
        [$user, $itemWithBoth] = $this->createUserWithPricedItem(
            cashPrice: '1000000',
            installmentPrice: '1200000',
        );

        $itemCashOnly = $this->createItemWithPrices(cashPrice: '500000', installmentPrice: null);

        $cartService = app(CartService::class);
        $cartService->addItem($user, $itemWithBoth);
        $cartService->addItem($user, $itemCashOnly);

        $cart = $cartService->getOrCreateCart($user);
        $result = $cartService->setSaleType($cart, PriceTypeEnum::Installment);

        $cart->refresh()->load('items');

        $this->assertSame(PriceTypeEnum::Installment, $cart->sale_type);
        $this->assertSame(1, $result['removed']);
        $this->assertCount(1, $cart->items);
        $this->assertSame('1200000.0000', (string) $cart->items->first()->unit_price);
    }

    #[Test]
    public function cash_checkout_requires_organization_and_skips_installments(): void
    {
        [$user, $item] = $this->createUserWithPricedItem(cashPrice: '1000000', installmentPrice: '1200000');
        $organization = Organization::query()->create([
            'code' => 'ORGCODE12345678',
            'is_active' => true,
        ]);

        $cartService = app(CartService::class);
        $cartService->addItem($user, $item, 1);

        $order = app(CheckoutService::class)->placeOrder($user, $organization->code);

        $this->assertSame(PriceTypeEnum::Cash, $order->sale_type);
        $this->assertNull($order->installment_plan_id);
        $this->assertSame(OrderStatusEnum::PendingApproval, $order->status);
        $this->assertSame('1000000.0000', (string) $order->total_payable);
        $this->assertCount(0, $order->installments);
        $this->assertCount(1, $order->items);
        $this->assertSame(0, CartItem::query()->count());
    }

    #[Test]
    public function installment_checkout_requires_eligible_plan(): void
    {
        [$user, $item] = $this->createUserWithPricedItem(cashPrice: '1000000', installmentPrice: '1200000');
        $organization = Organization::query()->create([
            'code' => 'ORGCODE87654321',
            'is_active' => true,
        ]);

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->setSaleType($cart, PriceTypeEnum::Installment);
        $cartService->addItem($user, $item->fresh(), 1);

        $this->expectException(ValidationException::class);

        app(CheckoutService::class)->placeOrder($user, $organization->code);
    }

    #[Test]
    public function installment_checkout_creates_schedule_when_plan_eligible(): void
    {
        [$user, $item] = $this->createUserWithPricedItem(cashPrice: '1000000', installmentPrice: '1000000');
        $organization = Organization::query()->create([
            'code' => 'ORGCODEABCDEF12',
            'is_active' => true,
        ]);

        $plan = InstallmentPlan::query()->create([
            'title' => '10 months',
            'organization_id' => $organization->id,
            'term_months' => 10,
            'down_payment_percent' => 0,
            'monthly_interest_percent' => 2,
            'max_financiable_amount' => 50000000,
            'down_payment_required_above' => null,
            'min_down_payment_percent' => 0,
            'min_order_amount' => 0,
            'priority' => 1,
            'is_active' => true,
        ]);

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->setSaleType($cart, PriceTypeEnum::Installment);
        $cartService->addItem($user, $item->fresh(), 1);

        $order = app(CheckoutService::class)->placeOrder($user, $organization->code, $plan->id);

        $this->assertSame(PriceTypeEnum::Installment, $order->sale_type);
        $this->assertSame($plan->id, $order->installment_plan_id);
        $this->assertGreaterThan(0, $order->installments->count());
        $this->assertSame(OrderStatusEnum::PendingApproval, $order->status);
    }

    /**
     * @return array{0: User, 1: Item}
     */
    protected function createUserWithPricedItem(?string $cashPrice, ?string $installmentPrice): array
    {
        $user = User::factory()->create();
        $item = $this->createItemWithPrices($cashPrice, $installmentPrice);

        return [$user, $item];
    }

    protected function createItemWithPrices(?string $cashPrice, ?string $installmentPrice): Item
    {
        $brand = Brand::query()->create([
            'title' => 'Brand '.uniqid(),
            'slug' => 'brand-'.uniqid(),
        ]);

        $category = Category::query()->create([
            'title' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
        ]);

        $item = Item::query()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'title' => 'Item '.uniqid(),
            'slug' => 'item-'.uniqid(),
            'is_active' => true,
            'is_purchasable' => true,
            'is_contact_price' => false,
            'stock' => 10,
            'is_main' => true,
        ]);

        if ($cashPrice !== null) {
            ItemPrice::query()->create([
                'item_id' => $item->id,
                'price_type' => PriceTypeEnum::Cash,
                'price' => $cashPrice,
                'sale_price' => $cashPrice,
                'is_active' => true,
            ]);
        }

        if ($installmentPrice !== null) {
            ItemPrice::query()->create([
                'item_id' => $item->id,
                'price_type' => PriceTypeEnum::Installment,
                'price' => $installmentPrice,
                'sale_price' => $installmentPrice,
                'is_active' => true,
            ]);
        }

        return $item->fresh();
    }
}
