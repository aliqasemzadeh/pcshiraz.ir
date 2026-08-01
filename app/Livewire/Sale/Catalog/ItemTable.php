<?php

namespace App\Livewire\Sale\Catalog;

use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class ItemTable extends PowerGridComponent
{
    public string $tableName = 'saleCatalogItemsTable';

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
        return Item::query()
            ->with(['brand', 'category', 'media', 'tags', 'activeCashPrice'])
            ->orderByDesc('id');
    }

    public function relationSearch(): array
    {
        return [
            'brand' => ['title'],
            'category' => ['title'],
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('image', function (Item $item) {
                $media = $item->getFirstMedia('product_image');

                if ($media === null) {
                    return '<span class="text-sm text-body">—</span>';
                }

                $url = $media->getUrl('thumb') ?: $media->getUrl();

                if ($url === '') {
                    return '<span class="text-sm text-body">—</span>';
                }

                return sprintf(
                    '<img src="%s" alt="%s" class="h-10 w-10 rounded object-contain bg-neutral-secondary-soft p-1" loading="lazy" />',
                    e($url),
                    e($item->title),
                );
            })
            ->add('title')
            ->add('category_title', fn (Item $item) => $item->category?->title ?? '—')
            ->add('brand_title', fn (Item $item) => $item->brand?->title ?? '—')
            ->add('color', function (Item $item) {
                $name = $item->color_name ?: '—';
                $code = $item->color_code;

                if ($code === null || $code === '') {
                    return e($name);
                }

                return sprintf(
                    '<span class="inline-flex items-center gap-2"><span class="inline-block h-4 w-4 rounded-full border border-default" style="background-color: %s"></span><span>%s</span></span>',
                    e($code),
                    e($name),
                );
            })
            ->add('color_name')
            ->add('is_main_label', function (Item $item) {
                return $item->is_main
                    ? '<span class="text-fg-success-strong">'.e(__('general.yes')).'</span>'
                    : '<span class="text-body">'.e(__('general.no')).'</span>';
            })
            ->add('is_active_label', function (Item $item) {
                return $item->is_active
                    ? '<span class="text-fg-success-strong">'.e(__('general.yes')).'</span>'
                    : '<span class="text-body">'.e(__('general.no')).'</span>';
            })
            ->add('is_purchasable_label', function (Item $item) {
                return $item->is_purchasable
                    ? '<span class="text-fg-success-strong">'.e(__('general.yes')).'</span>'
                    : '<span class="text-body">'.e(__('general.no')).'</span>';
            })
            ->add('item_type_label', function (Item $item) {
                return $item->item_type?->label() ?? '—';
            })
            ->add('created_at_formatted', function (Item $item) {
                if ($item->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($item->created_at)->format('Y/m/d H:i');
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

            Column::make(__('general.category'), 'category_title'),

            Column::make(__('general.brand'), 'brand_title'),

            Column::make(__('general.color'), 'color', 'color_name')
                ->searchable()
                ->visibleInExport(false),

            Column::make(__('general.is_main'), 'is_main_label', 'is_main')
                ->sortable(),

            Column::make(__('app.is_active'), 'is_active_label', 'is_active')
                ->sortable(),

            Column::make(__('app.is_purchasable'), 'is_purchasable_label', 'is_purchasable')
                ->sortable(),

            Column::make(__('general.item_type'), 'item_type_label', 'item_type')
                ->sortable(),

            Column::make(__('general.created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(Item $row): View
    {
        return view('components.sale.catalog.item.row-actions', [
            'row' => $row,
        ]);
    }
}
