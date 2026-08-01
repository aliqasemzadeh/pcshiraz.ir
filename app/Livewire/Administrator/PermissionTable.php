<?php

namespace App\Livewire\Administrator;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Spatie\Permission\Models\Permission;

final class PermissionTable extends PowerGridComponent
{
    public string $tableName = 'administratorPermissionsTable';

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
        return Permission::query()->orderBy('name');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('label', function (Permission $permission) {
                $key = 'permissions.'.$permission->name;
                $translated = __($key);

                return $translated !== $key ? $translated : '—';
            })
            ->add('guard_name')
            ->add('created_at_formatted', function (Permission $permission) {
                if ($permission->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($permission->created_at)->format('Y/m/d H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('general.name'), 'name')
                ->searchable()
                ->sortable(),

            Column::make(__('general.title'), 'label'),

            Column::make(__('general.guard_name'), 'guard_name')
                ->searchable()
                ->sortable(),

            Column::make(__('general.created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(Permission $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'administrator.permission.edit',
            'deleteModal' => 'administrator.permission.delete',
            'idProp' => 'permissionId',
        ]);
    }
}
