<?php

namespace App\Livewire\Administrator;

use App\Models\Domain;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class DomainTable extends PowerGridComponent
{
    public string $tableName = 'administratorDomainsTable';

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
        return Domain::query()
            ->leftJoin('users', 'users.id', '=', 'domains.user_id')
            ->select('domains.*')
            ->addSelect('users.mobile as user_mobile')
            ->orderByDesc('domains.id');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('user_mobile')
            ->add('title')
            ->add('domain')
            ->add('description')
            ->add('created_at_formatted', function (Domain $domain) {
                if ($domain->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($domain->created_at)->format('Y/m/d H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('general.mobile'), 'user_mobile', 'users.mobile')
                ->searchable()
                ->sortable(),

            Column::make(__('general.title'), 'title')
                ->searchable()
                ->sortable(),

            Column::make(__('general.domain_name'), 'domain')
                ->searchable()
                ->sortable(),

            Column::make(__('general.description'), 'description')
                ->searchable()
                ->sortable(),

            Column::make(__('general.created_at'), 'created_at_formatted', 'domains.created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(Domain $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'administrator.domain.edit',
            'deleteModal' => 'administrator.domain.delete',
            'idProp' => 'domainId',
        ]);
    }
}
