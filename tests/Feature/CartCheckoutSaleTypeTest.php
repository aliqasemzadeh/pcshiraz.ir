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
use Livewire\Livewire;
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
    public function set_sale_type_reprices_items_and_keeps_cash_only_as_fallback(): void
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

        $cart->refresh()->load(['items.itemPrice']);

        $this->assertSame(PriceTypeEnum::Installment, $cart->sale_type);
        $this->assertSame(0, $result['removed']);
        $this->assertSame(1, $result['cash_only']);
        $this->assertCount(2, $cart->items);

        $installmentLine = $cart->items->firstWhere('item_id', $itemWithBoth->id);
        $cashLine = $cart->items->firstWhere('item_id', $itemCashOnly->id);

        $this->assertSame('1200000.0000', (string) $installmentLine->unit_price);
        $this->assertSame(PriceTypeEnum::Installment, $installmentLine->itemPrice->price_type);
        $this->assertSame('500000.0000', (string) $cashLine->unit_price);
        $this->assertSame(PriceTypeEnum::Cash, $cashLine->itemPrice->price_type);

        $breakdown = $cartService->breakdown($cart);
        $this->assertSame('1200000.0000', $breakdown['installment_subtotal']);
        $this->assertSame('500000.0000', $breakdown['cash_only_subtotal']);
        $this->assertSame('1700000.0000', $breakdown['subtotal']);
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
        $this->assertSame(PriceTypeEnum::Cash, $order->items->first()->price_type);
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

    #[Test]
    public function mixed_installment_checkout_adds_cash_only_to_down_payment(): void
    {
        [$user, $installmentItem] = $this->createUserWithPricedItem(
            cashPrice: '1000000',
            installmentPrice: '1000000',
        );
        $cashOnlyItem = $this->createItemWithPrices(cashPrice: '500000', installmentPrice: null);

        $organization = Organization::query()->create([
            'code' => 'ORGMIXED1234567',
            'is_active' => true,
        ]);

        $plan = InstallmentPlan::query()->create([
            'title' => '10 months 10%',
            'organization_id' => $organization->id,
            'term_months' => 10,
            'down_payment_percent' => 10,
            'monthly_interest_percent' => 0,
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
        $cartService->addItem($user, $installmentItem->fresh(), 1);
        $cartService->addItem($user, $cashOnlyItem, 1);

        $order = app(CheckoutService::class)->placeOrder($user, $organization->code, $plan->id);

        $this->assertSame('500000.0000', (string) $order->cash_only_subtotal);
        $this->assertSame('1000000.0000', (string) $order->installment_subtotal);
        $this->assertSame('100000.0000', (string) $order->plan_down_payment_amount);
        $this->assertSame('600000.0000', (string) $order->down_payment_amount);
        $this->assertSame('900000.0000', (string) $order->financed_amount);

        $priceTypes = $order->items->pluck('price_type')->map(fn ($type) => $type->value)->sort()->values()->all();
        $this->assertSame(['cash', 'installment'], $priceTypes);

        $downPaymentRow = $order->installments->firstWhere('sequence', 0);
        $this->assertNotNull($downPaymentRow);
        $this->assertSame('600000.0000', (string) $downPaymentRow->total_amount);
    }

    #[Test]
    public function cart_page_down_payment_follows_selected_installment_plan(): void
    {
        [$user, $installmentItem] = $this->createUserWithPricedItem(
            cashPrice: '1000000',
            installmentPrice: '1000000',
        );
        $cashOnlyItem = $this->createItemWithPrices(cashPrice: '500000', installmentPrice: null);

        $organization = Organization::query()->create([
            'code' => 'ORGCARTPLAN1234',
            'is_active' => true,
        ]);

        $tenPercentPlan = InstallmentPlan::query()->create([
            'title' => '10 percent plan',
            'organization_id' => $organization->id,
            'term_months' => 10,
            'down_payment_percent' => 10,
            'monthly_interest_percent' => 0,
            'max_financiable_amount' => 50000000,
            'down_payment_required_above' => null,
            'min_down_payment_percent' => 0,
            'min_order_amount' => 0,
            'priority' => 2,
            'is_active' => true,
        ]);

        $twentyPercentPlan = InstallmentPlan::query()->create([
            'title' => '20 percent plan',
            'organization_id' => $organization->id,
            'term_months' => 10,
            'down_payment_percent' => 20,
            'monthly_interest_percent' => 0,
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
        $cartService->addItem($user, $installmentItem->fresh(), 1);
        $cartService->addItem($user, $cashOnlyItem, 1);

        $component = Livewire::actingAs($user)
            ->test('pages::shop.cart.index')
            ->set('organization_code', $organization->code)
            ->call('validateOrganizationCode')
            ->assertSet('installment_plan_id', $tenPercentPlan->id)
            ->assertSee(number_format(100000))
            ->assertSee(number_format(600000));

        $preview = $component->instance()->selectedPlanPreview;
        $this->assertSame('100000.0000', $preview['plan_down_payment_amount']);
        $this->assertSame('600000.0000', $preview['down_payment_amount']);

        $component->set('installment_plan_id', $twentyPercentPlan->id);

        $preview = $component->instance()->selectedPlanPreview;
        $this->assertSame('200000.0000', $preview['plan_down_payment_amount']);
        $this->assertSame('700000.0000', $preview['down_payment_amount']);
        $component->assertSee(number_format(200000))->assertSee(number_format(700000));
    }

    #[Test]
    public function all_cash_only_installment_cart_cannot_checkout(): void
    {
        $user = User::factory()->create();
        $cashOnlyItem = $this->createItemWithPrices(cashPrice: '500000', installmentPrice: null);
        $organization = Organization::query()->create([
            'code' => 'ORGCASHONLY1234',
            'is_active' => true,
        ]);

        $plan = InstallmentPlan::query()->create([
            'title' => 'Plan',
            'organization_id' => $organization->id,
            'term_months' => 10,
            'down_payment_percent' => 0,
            'monthly_interest_percent' => 0,
            'max_financiable_amount' => 50000000,
            'is_active' => true,
        ]);

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->setSaleType($cart, PriceTypeEnum::Installment);
        $cartService->addItem($user, $cashOnlyItem, 1);

        $this->expectException(ValidationException::class);

        app(CheckoutService::class)->placeOrder($user, $organization->code, $plan->id);
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
