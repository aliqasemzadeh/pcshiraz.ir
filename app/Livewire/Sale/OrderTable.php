<?php

namespace App\Livewire\Sale;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class OrderTable extends PowerGridComponent
{
    public string $tableName = 'saleOrdersTable';

    public function setUp(): array
    {
        return [
            PowerGrid::header()->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage(config('main.per_page'))
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Order::query()
            ->with(['organization', 'user'])
            ->orderByDesc('id');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('order_number')
            ->add('organization_code', fn (Order $order) => e($order->organization?->code ?? '—'))
            ->add('buyer', fn (Order $order) => e($order->user?->mobile ?? '—'))
            ->add('sale_type_label', fn (Order $order) => e($order->sale_type->label()))
            ->add('status_label', fn (Order $order) => e($order->status->label()))
            ->add('total_amount_formatted', fn (Order $order) => number_format((float) $order->total_amount))
            ->add('created_at_formatted', function (Order $order) {
                if ($order->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($order->created_at)->format('Y/m/d H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('general.order_number'), 'order_number')
                ->searchable()
                ->sortable(),

            Column::make(__('general.organization_code'), 'organization_code'),

            Column::make(__('general.buyer'), 'buyer'),

            Column::make(__('general.sale_type'), 'sale_type_label'),

            Column::make(__('general.status'), 'status_label'),

            Column::make(__('general.total_amount'), 'total_amount_formatted', 'total_amount')
                ->sortable(),

            Column::make(__('general.created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(Order $row): View
    {
        return view('components.powergrid.order-actions', [
            'row' => $row,
            'editModal' => 'sale.order.edit',
            'viewModal' => 'sale.order.view',
            'idProp' => 'orderId',
        ]);
    }
}
