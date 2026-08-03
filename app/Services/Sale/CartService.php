<?php

namespace App\Services\Sale;

use App\Enums\PriceTypeEnum;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * @return list<PriceTypeEnum>
     */
    public function allowedSaleTypes(): array
    {
        return [
            PriceTypeEnum::Cash,
            PriceTypeEnum::Installment,
        ];
    }

    public function getOrCreateCart(User $user): Cart
    {
        return Cart::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['sale_type' => PriceTypeEnum::Cash],
        );
    }

    /**
     * @return array{removed: int}
     */
    public function setSaleType(Cart $cart, PriceTypeEnum $saleType): array
    {
        if (! in_array($saleType, $this->allowedSaleTypes(), true)) {
            throw ValidationException::withMessages([
                'sale_type' => __('app.invalid_sale_type'),
            ]);
        }

        if ($cart->sale_type === $saleType) {
            return ['removed' => 0];
        }

        return DB::transaction(function () use ($cart, $saleType) {
            $cart->update(['sale_type' => $saleType]);

            return $this->repriceCart($cart->fresh(['items.item']));
        });
    }

    /**
     * @return array{removed: int}
     */
    public function repriceCart(Cart $cart): array
    {
        $cart->loadMissing('items.item');
        $removed = 0;

        foreach ($cart->items as $cartItem) {
            $item = $cartItem->item;

            if ($item === null) {
                $cartItem->delete();
                $removed++;

                continue;
            }

            $itemPrice = $this->resolvePrice($item, $cart->sale_type);

            if ($itemPrice === null) {
                $cartItem->delete();
                $removed++;

                continue;
            }

            $cartItem->update([
                'item_price_id' => $itemPrice->id,
                'unit_price' => $itemPrice->sale_price,
            ]);
        }

        return ['removed' => $removed];
    }

    public function addItem(User $user, Item $item, int $quantity = 1): CartItem
    {
        if (! $item->is_active || ! $item->is_purchasable || $item->is_contact_price) {
            throw ValidationException::withMessages([
                'item' => __('general.item_not_purchasable'),
            ]);
        }

        $cart = $this->getOrCreateCart($user);
        $itemPrice = $this->resolvePrice($item, $cart->sale_type);

        if ($itemPrice === null) {
            throw ValidationException::withMessages([
                'item' => __('general.item_price_not_found'),
            ]);
        }

        $quantity = max(1, $quantity);

        return DB::transaction(function () use ($cart, $item, $itemPrice, $quantity) {
            $existing = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('item_id', $item->id)
                ->first();

            if ($existing !== null) {
                $existing->update([
                    'quantity' => $existing->quantity + $quantity,
                    'item_price_id' => $itemPrice->id,
                    'unit_price' => $itemPrice->sale_price,
                ]);

                return $existing->refresh();
            }

            return CartItem::query()->create([
                'cart_id' => $cart->id,
                'item_id' => $item->id,
                'item_price_id' => $itemPrice->id,
                'quantity' => $quantity,
                'unit_price' => $itemPrice->sale_price,
            ]);
        });
    }

    public function updateQuantity(CartItem $cartItem, int $quantity): void
    {
        if ($quantity < 1) {
            $cartItem->delete();

            return;
        }

        $cartItem->update(['quantity' => $quantity]);
    }

    public function removeItem(CartItem $cartItem): void
    {
        $cartItem->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function subtotal(Cart $cart): string
    {
        $cart->loadMissing('items');

        $total = '0.0000';

        foreach ($cart->items as $item) {
            $total = bcadd($total, bcmul((string) $item->unit_price, (string) $item->quantity, 4), 4);
        }

        return $total;
    }

    protected function resolvePrice(Item $item, PriceTypeEnum $type): ?ItemPrice
    {
        return ItemPrice::query()
            ->where('item_id', $item->id)
            ->where('price_type', $type)
            ->where('is_active', true)
            ->first();
    }
}
