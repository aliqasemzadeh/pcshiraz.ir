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
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    public function addItem(User $user, Item $item, int $quantity = 1, ?PriceTypeEnum $priceType = null): CartItem
    {
        if (! $item->is_active || ! $item->is_purchasable || $item->is_contact_price) {
            throw ValidationException::withMessages([
                'item' => __('general.item_not_purchasable'),
            ]);
        }

        $priceType ??= PriceTypeEnum::Corporate;
        $itemPrice = $this->resolvePrice($item, $priceType);

        if ($itemPrice === null) {
            $itemPrice = $this->resolvePrice($item, PriceTypeEnum::Installment)
                ?? $this->resolvePrice($item, PriceTypeEnum::Cash);
        }

        if ($itemPrice === null) {
            throw ValidationException::withMessages([
                'item' => __('general.item_price_not_found'),
            ]);
        }

        $quantity = max(1, $quantity);
        $cart = $this->getOrCreateCart($user);

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
