<?php

namespace App\Livewire\Administrator;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Spatie\Permission\Models\Role;

final class RoleTable extends PowerGridComponent
{
    public string $tableName = 'administratorRolesTable';

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
        return Role::query()
            ->withCount(['permissions', 'users'])
            ->orderBy('name');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('guard_name')
            ->add('permissions_count')
            ->add('users_count')
            ->add('created_at_formatted', function (Role $role) {
                if ($role->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($role->created_at)->format('Y/m/d H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('general.name'), 'name')
                ->searchable()
                ->sortable(),

            Column::make(__('general.guard_name'), 'guard_name')
                ->searchable()
                ->sortable(),

            Column::make(__('general.permissions'), 'permissions_count')
                ->sortable(),

            Column::make(__('general.users'), 'users_count')
                ->sortable(),

            Column::make(__('general.created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(Role $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'administrator.role.edit',
            'deleteModal' => 'administrator.role.delete',
            'idProp' => 'roleId',
            'extraModals' => [
                [
                    'modal' => 'administrator.role.permissions',
                    'icon' => 'key-round',
                    'label' => __('general.assign_permissions'),
                    'color' => 'indigo',
                ],
                [
                    'modal' => 'administrator.role.users',
                    'icon' => 'users',
                    'label' => __('general.assign_users'),
                    'color' => 'teal',
                ],
            ],
        ]);
    }
}
