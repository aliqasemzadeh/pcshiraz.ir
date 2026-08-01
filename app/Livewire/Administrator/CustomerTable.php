<?php

namespace App\Livewire\Administrator;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CustomerTable extends PowerGridComponent
{
    public string $tableName = 'administratorCustomersTable';

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
        return Customer::query()
            ->leftJoin('users', 'users.id', '=', 'customers.user_id')
            ->leftJoin('domains', 'domains.id', '=', 'customers.domain_id')
            ->select('customers.*')
            ->addSelect('users.mobile as user_mobile')
            ->addSelect('domains.title as domain_title')
            ->addSelect('domains.domain as domain_host')
            ->orderByDesc('customers.id');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('user_mobile')
            ->add('domain_label', fn (Customer $customer) => trim(($customer->domain_title ?? '').' '.($customer->domain_host ? '('.$customer->domain_host.')' : '')) ?: '—')
            ->add('first_name')
            ->add('last_name')
            ->add('national_code')
            ->add('birth_date_formatted', function (Customer $customer) {
                if ($customer->birth_date === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($customer->birth_date)->format('Y/m/d');
            })
            ->add('created_at_formatted', function (Customer $customer) {
                if ($customer->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($customer->created_at)->format('Y/m/d H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('general.mobile'), 'user_mobile', 'users.mobile')
                ->searchable()
                ->sortable(),

            Column::make(__('general.domains'), 'domain_label', 'domains.title')
                ->searchable()
                ->sortable(),

            Column::make(__('general.first_name'), 'first_name')
                ->searchable()
                ->sortable(),

            Column::make(__('general.last_name'), 'last_name')
                ->searchable()
                ->sortable(),

            Column::make(__('general.national_code'), 'national_code')
                ->searchable()
                ->sortable(),

            Column::make(__('general.birth_date'), 'birth_date_formatted', 'customers.birth_date')
                ->sortable(),

            Column::make(__('general.created_at'), 'created_at_formatted', 'customers.created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(Customer $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'administrator.customer.edit',
            'deleteModal' => 'administrator.customer.delete',
            'idProp' => 'customerId',
        ]);
    }
}
