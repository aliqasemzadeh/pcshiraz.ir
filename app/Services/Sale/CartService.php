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
     * @return array{removed: int, cash_only: int}
     */
    public function setSaleType(Cart $cart, PriceTypeEnum $saleType): array
    {
        if (! in_array($saleType, $this->allowedSaleTypes(), true)) {
            throw ValidationException::withMessages([
                'sale_type' => __('app.invalid_sale_type'),
            ]);
        }

        if ($cart->sale_type === $saleType) {
            return ['removed' => 0, 'cash_only' => 0];
        }

        return DB::transaction(function () use ($cart, $saleType) {
            $cart->update(['sale_type' => $saleType]);

            return $this->repriceCart($cart->fresh(['items.item']));
        });
    }

    /**
     * @return array{removed: int, cash_only: int}
     */
    public function repriceCart(Cart $cart): array
    {
        $cart->loadMissing('items.item');
        $removed = 0;
        $cashOnly = 0;

        foreach ($cart->items as $cartItem) {
            $item = $cartItem->item;

            if ($item === null) {
                $cartItem->delete();
                $removed++;

                continue;
            }

            $itemPrice = $this->resolvePriceForSaleType($item, $cart->sale_type);

            if ($itemPrice === null) {
                $cartItem->delete();
                $removed++;

                continue;
            }

            $cartItem->update([
                'item_price_id' => $itemPrice->id,
                'unit_price' => $itemPrice->sale_price,
            ]);

            if (
                $cart->sale_type === PriceTypeEnum::Installment
                && $itemPrice->price_type === PriceTypeEnum::Cash
            ) {
                $cashOnly++;
            }
        }

        return ['removed' => $removed, 'cash_only' => $cashOnly];
    }

    public function addItem(User $user, Item $item, int $quantity = 1): CartItem
    {
        if (! $item->is_active || ! $item->is_purchasable || $item->is_contact_price) {
            throw ValidationException::withMessages([
                'item' => __('general.item_not_purchasable'),
            ]);
        }

        $cart = $this->getOrCreateCart($user);
        $itemPrice = $this->resolvePriceForSaleType($item, $cart->sale_type);

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
        return $this->breakdown($cart)['subtotal'];
    }

    /**
     * @return array{
     *     subtotal: string,
     *     cash_only_subtotal: string,
     *     installment_subtotal: string,
     *     cash_only_count: int
     * }
     */
    public function breakdown(Cart $cart): array
    {
        $cart->loadMissing(['items.itemPrice', 'sale_type']);

        $subtotal = '0.0000';
        $cashOnlySubtotal = '0.0000';
        $installmentSubtotal = '0.0000';
        $cashOnlyCount = 0;

        foreach ($cart->items as $cartItem) {
            $lineTotal = bcmul((string) $cartItem->unit_price, (string) $cartItem->quantity, 4);
            $subtotal = bcadd($subtotal, $lineTotal, 4);

            if ($this->lineIsCashOnly($cart, $cartItem)) {
                $cashOnlySubtotal = bcadd($cashOnlySubtotal, $lineTotal, 4);
                $cashOnlyCount++;
            } else {
                $installmentSubtotal = bcadd($installmentSubtotal, $lineTotal, 4);
            }
        }

        return [
            'subtotal' => $subtotal,
            'cash_only_subtotal' => $cashOnlySubtotal,
            'installment_subtotal' => $installmentSubtotal,
            'cash_only_count' => $cashOnlyCount,
        ];
    }

    public function lineIsCashOnly(Cart $cart, CartItem $cartItem): bool
    {
        if ($cart->sale_type !== PriceTypeEnum::Installment) {
            return false;
        }

        $cartItem->loadMissing('itemPrice');

        return $cartItem->itemPrice?->price_type === PriceTypeEnum::Cash;
    }

    public function linePriceType(Cart $cart, CartItem $cartItem): PriceTypeEnum
    {
        if ($this->lineIsCashOnly($cart, $cartItem)) {
            return PriceTypeEnum::Cash;
        }

        $cartItem->loadMissing('itemPrice');

        return $cartItem->itemPrice?->price_type ?? $cart->sale_type ?? PriceTypeEnum::Cash;
    }

    protected function resolvePriceForSaleType(Item $item, PriceTypeEnum $type): ?ItemPrice
    {
        $primary = $this->resolvePrice($item, $type);

        if ($primary !== null) {
            return $primary;
        }

        if ($type === PriceTypeEnum::Installment) {
            return $this->resolvePrice($item, PriceTypeEnum::Cash);
        }

        return null;
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
