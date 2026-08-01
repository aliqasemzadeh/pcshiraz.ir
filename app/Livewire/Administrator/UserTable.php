<?php

namespace App\Livewire\Administrator;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class UserTable extends PowerGridComponent
{
    public string $tableName = 'administratorUsersTable';

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
        return User::query()->orderByDesc('id');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('mobile')
            ->add('first_name')
            ->add('last_name')
            ->add('full_name', fn (User $user) => $user->full_name !== '' ? $user->full_name : '—')
            ->add('created_at_formatted', function (User $user) {
                if ($user->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($user->created_at)->format('Y/m/d H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('general.mobile'), 'mobile')
                ->searchable()
                ->sortable(),

            Column::make(__('general.first_name').' / '.__('general.last_name'), 'full_name'),

            Column::make(__('general.first_name'), 'first_name')
                ->searchable()
                ->hidden(),

            Column::make(__('general.last_name'), 'last_name')
                ->searchable()
                ->hidden(),

            Column::make(__('general.created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(User $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'administrator.user.edit',
            'deleteModal' => 'administrator.user.delete',
            'idProp' => 'userId',
            'extraModals' => [
                [
                    'modal' => 'administrator.user.roles',
                    'icon' => 'shield',
                    'label' => __('general.assign_roles'),
                    'color' => 'purple',
                ],
                [
                    'modal' => 'administrator.user.permissions',
                    'icon' => 'key-round',
                    'label' => __('general.assign_permissions'),
                    'color' => 'orange',
                ],
            ],
        ]);
    }
}
