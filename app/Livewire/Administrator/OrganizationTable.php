<?php

namespace App\Livewire\Administrator;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class OrganizationTable extends PowerGridComponent
{
    public string $tableName = 'administratorOrganizationsTable';

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
        return Organization::query()
            ->withCount('approvers')
            ->orderByDesc('id');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('code')
            ->add('internal_note')
            ->add('is_active_label', fn (Organization $org) => $org->is_active
                ? '<span class="text-green-600">'.e(__('general.active')).'</span>'
                : '<span class="text-red-600">'.e(__('general.inactive')).'</span>')
            ->add('approvers_count')
            ->add('created_at_formatted', function (Organization $org) {
                if ($org->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($org->created_at)->format('Y/m/d H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('general.organization_code'), 'code')
                ->searchable()
                ->sortable(),

            Column::make(__('general.internal_note'), 'internal_note')
                ->searchable(),

            Column::make(__('general.active'), 'is_active_label'),

            Column::make(__('general.approvers'), 'approvers_count')
                ->sortable(),

            Column::make(__('general.created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(Organization $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'administrator.organization.edit',
            'deleteModal' => 'administrator.organization.delete',
            'idProp' => 'organizationId',
            'extraModals' => [
                [
                    'modal' => 'administrator.organization.users',
                    'icon' => 'users',
                    'label' => __('general.assign_approvers'),
                    'color' => 'purple',
                ],
            ],
        ]);
    }
}
