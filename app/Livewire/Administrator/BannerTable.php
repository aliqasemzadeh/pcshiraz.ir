<?php

namespace App\Livewire\Administrator;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class BannerTable extends PowerGridComponent
{
    public string $tableName = 'administratorBannersTable';

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
        return Banner::query()
            ->with('media')
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('image', function (Banner $banner) {
                $media = $banner->getFirstMedia('banner_image');

                if ($media === null) {
                    return '<span class="text-sm text-body">—</span>';
                }

                $url = $media->getUrl('thumb') ?: $media->getUrl();

                if ($url === '') {
                    return '<span class="text-sm text-body">—</span>';
                }

                return sprintf(
                    '<img src="%s" alt="%s" class="h-12 w-20 rounded object-cover bg-neutral-secondary-soft" loading="lazy" />',
                    e($url),
                    e($banner->title),
                );
            })
            ->add('title')
            ->add('link_url')
            ->add('sort_order')
            ->add('is_active_label', function (Banner $banner) {
                return $banner->is_active
                    ? '<span class="text-sm text-green-600 dark:text-green-400">'.e(__('general.active')).'</span>'
                    : '<span class="text-sm text-body">'.e(__('general.inactive')).'</span>';
            })
            ->add('clicks_count')
            ->add('created_at_formatted', function (Banner $banner) {
                if ($banner->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($banner->created_at)->format('Y/m/d H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make(__('general.image'), 'image')
                ->visibleInExport(false),

            Column::make(__('general.title'), 'title')
                ->searchable()
                ->sortable(),

            Column::make(__('general.link_url'), 'link_url')
                ->searchable()
                ->sortable(),

            Column::make(__('general.sort_order'), 'sort_order')
                ->sortable(),

            Column::make(__('general.active'), 'is_active_label', 'is_active')
                ->sortable(),

            Column::make(__('general.clicks_count'), 'clicks_count')
                ->sortable(),

            Column::make(__('general.created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(Banner $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'administrator.banner.edit',
            'deleteModal' => 'administrator.banner.delete',
            'idProp' => 'bannerId',
        ]);
    }
}
