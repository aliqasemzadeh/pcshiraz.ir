<?php

namespace App\Livewire\Sale\Catalog;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class BrandTable extends PowerGridComponent
{
    public string $tableName = 'saleCatalogBrandsTable';

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
        return Brand::query()
            ->with('media')
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('logo', function (Brand $brand) {
                $media = $brand->getFirstMedia('logo_image');

                if ($media === null) {
                    return '<span class="text-sm text-body">—</span>';
                }

                $url = $media->mime_type === 'image/svg+xml'
                    ? $media->getUrl()
                    : ($media->getUrl('thumb') ?: $media->getUrl());

                if ($url === '') {
                    return '<span class="text-sm text-body">—</span>';
                }

                return sprintf(
                    '<img src="%s" alt="%s" class="h-10 w-10 rounded object-contain bg-neutral-secondary-soft p-1" loading="lazy" />',
                    e($url),
                    e($brand->title),
                );
            })
            ->add('title')
            ->add('slug')
            ->add('seo_title')
            ->add('sort_order')
            ->add('created_at_formatted', function (Brand $brand) {
                if ($brand->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($brand->created_at)->format('Y/m/d H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('general.logo'), 'logo')
                ->visibleInExport(false),

            Column::make(__('general.title'), 'title')
                ->searchable()
                ->sortable(),

            Column::make(__('general.slug'), 'slug')
                ->searchable()
                ->sortable(),

            Column::make(__('general.seo_title'), 'seo_title')
                ->searchable()
                ->sortable(),

            Column::make(__('general.sort_order'), 'sort_order')
                ->sortable(),

            Column::make(__('general.created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(Brand $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'sale.catalog.brand.edit',
            'deleteModal' => 'sale.catalog.brand.delete',
            'idProp' => 'brandId',
        ]);
    }
}
