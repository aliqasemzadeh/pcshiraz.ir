<?php

namespace App\Livewire\Forms;

use App\Enums\PriceTypeEnum;
use App\Models\Item;
use App\Models\ItemPrice;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ItemPriceForm extends Form
{
    public string $price_type = PriceTypeEnum::Cash->value;

    public string $price = '';

    public string $sale_price = '';

    public string|int|null $sales_cap = null;

    public bool $is_active = true;

    public function setType(PriceTypeEnum|string $type): void
    {
        $this->price_type = $type instanceof PriceTypeEnum ? $type->value : $type;
        $this->price = '';
        $this->sale_price = '';
        $this->sales_cap = null;
        $this->is_active = true;
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
            'is_active' => __('general.active'),
        ];
    }

    public function store(Item $item): ItemPrice
    {
        if ($this->sales_cap === '' || $this->sales_cap === null) {
            $this->sales_cap = null;
        }

        if ($this->price === '') {
            $this->price = '0';
        }

        if ($this->sale_price === '') {
            $this->sale_price = $this->price;
        }

        $this->validate();

        return ItemPrice::query()->create([
            'item_id' => $item->id,
            'price_type' => $this->price_type,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'sales_cap' => $this->sales_cap,
            'is_active' => $this->is_active,
        ]);
    }
}
