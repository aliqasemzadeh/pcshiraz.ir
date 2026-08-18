<?php

namespace App\Livewire\Forms;

use App\Enums\PriceTypeEnum;
use App\Models\Item;
use App\Models\ItemPrice;
use App\Support\Price;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ItemPriceForm extends Form
{
    public string $price_type = PriceTypeEnum::Cash->value;

    public string $price = '';

    public string $sale_price = '';

    public string|int|null $sales_cap = null;

    public string|int $stock = 0;

    public bool $is_active = true;

    public function setType(PriceTypeEnum|string $type): void
    {
        $this->price_type = $type instanceof PriceTypeEnum ? $type->value : $type;
        $this->price = '';
        $this->sale_price = '';
        $this->sales_cap = null;
        $this->is_active = true;
    }

    public function setStockFromItem(Item $item): void
    {
        $this->stock = (int) $item->stock;
    }

    public function fillAmounts(string|int|float|null $price, string|int|float|null $salePrice): void
    {
        $this->price = $price !== null && $price !== '' ? (string) Price::toDisplay($price) : '';
        $this->sale_price = $salePrice !== null && $salePrice !== '' ? (string) Price::toDisplay($salePrice) : '';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'price_type' => ['required', Rule::enum(PriceTypeEnum::class)],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'sales_cap' => ['nullable', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'price_type' => __('general.price_type'),
            'price' => __('general.price'),
            'sale_price' => __('general.sale_price'),
            'sales_cap' => __('general.sales_cap'),
            'stock' => __('app.stock'),
            'is_active' => __('general.active'),
        ];
    }

    public function store(Item $item, bool $alsoSetInstallment = false): ItemPrice
    {
        $this->price = $this->unmaskMoney((string) $this->price);
        $this->sale_price = $this->unmaskMoney((string) $this->sale_price);

        if ($this->sales_cap === '' || $this->sales_cap === null) {
            $this->sales_cap = null;
        }

        if ($this->price === '') {
            $this->price = '0';
        }

        if ($this->sale_price === '') {
            $this->sale_price = $this->price;
        }

        if ($this->stock === '' || $this->stock === null) {
            $this->stock = 0;
        }

        $this->validate();

        $stock = (int) $this->stock;
        $storedPrice = (string) Price::fromDisplay($this->price);
        $storedSalePrice = (string) Price::fromDisplay($this->sale_price);

        $price = ItemPrice::query()->create([
            'item_id' => $item->id,
            'price_type' => $this->price_type,
            'price' => $storedPrice,
            'sale_price' => $storedSalePrice,
            'sales_cap' => $this->sales_cap,
            'is_active' => $this->is_active,
        ]);

        if (
            $alsoSetInstallment
            && $this->price_type === PriceTypeEnum::Cash->value
        ) {
            ItemPrice::query()->create([
                'item_id' => $item->id,
                'price_type' => PriceTypeEnum::Installment,
                'price' => $storedPrice,
                'sale_price' => $storedSalePrice,
                'sales_cap' => $this->sales_cap,
                'is_active' => $this->is_active,
            ]);
        }

        $item->update([
            'stock' => $stock,
            'is_purchasable' => $stock > 0,
        ]);

        return $price;
    }

    protected function unmaskMoney(string $value): string
    {
        return str_replace([',', ' '], '', $value);
    }
}
