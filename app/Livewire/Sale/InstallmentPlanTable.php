<?php

namespace App\Livewire\Sale;

use App\Models\InstallmentPlan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class InstallmentPlanTable extends PowerGridComponent
{
    public string $tableName = 'saleInstallmentPlansTable';

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
        return InstallmentPlan::query()
            ->with('organization')
            ->orderByDesc('priority')
            ->orderByDesc('id');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('title')
            ->add('scope', fn (InstallmentPlan $plan) => $plan->organization_id
                ? e($plan->organization?->code ?? '—')
                : e(__('general.global_plan')))
            ->add('term_months')
            ->add('down_payment_percent')
            ->add('monthly_interest_percent')
            ->add('order_amount_range', function (InstallmentPlan $plan) {
                $min = $plan->min_order_amount !== null ? format_price((float) $plan->min_order_amount) : '—';
                $max = $plan->max_order_amount !== null ? format_price((float) $plan->max_order_amount) : '—';

                return e($min.' – '.$max);
            })
            ->add('is_active_label', fn (InstallmentPlan $plan) => $plan->is_active
                ? '<span class="text-green-600">'.e(__('general.active')).'</span>'
                : '<span class="text-red-600">'.e(__('general.inactive')).'</span>')
            ->add('created_at_formatted', function (InstallmentPlan $plan) {
                if ($plan->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($plan->created_at)->format('Y/m/d H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('general.title'), 'title')
                ->searchable()
                ->sortable(),

            Column::make(__('general.scope'), 'scope'),

            Column::make(__('general.term_months'), 'term_months')
                ->sortable(),

            Column::make(__('general.down_payment_percent'), 'down_payment_percent')
                ->sortable(),

            Column::make(__('general.monthly_interest_percent'), 'monthly_interest_percent')
                ->sortable(),

            Column::make(__('general.order_amount_range'), 'order_amount_range'),

            Column::make(__('general.active'), 'is_active_label'),

            Column::make(__('general.created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(InstallmentPlan $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'sale.installment-plan.edit',
            'deleteModal' => 'sale.installment-plan.delete',
            'idProp' => 'installmentPlanId',
            'extraModals' => $row->organization_id === null ? [
                [
                    'modal' => 'sale.installment-plan.organizations',
                    'icon' => 'users',
                    'label' => __('general.assign_organizations'),
                    'color' => 'teal',
                ],
            ] : [],
        ]);
    }
}
