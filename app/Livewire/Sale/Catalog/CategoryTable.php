<?php

namespace App\Livewire\Sale\Catalog;

use App\Models\Category;
use App\Support\CurrentDomain;
use Illuminate\Database\Eloquent\Builder;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class CategoryTable extends PowerGridComponent
{
    public string $tableName = 'saleCatalogCategoriesTable';

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
        $domainId = CurrentDomain::get()?->id;

        return Category::query()
            ->when($domainId, fn (Builder $query) => $query->where('domain_id', $domainId))
            ->when(! $domainId, fn (Builder $query) => $query->whereRaw('1 = 0'))
            ->with('media')
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('logo', function (Category $category) {
                $media = $category->getFirstMedia('logo_image');

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
                    e($category->title),
                );
            })
            ->add('title')
            ->add('slug')
            ->add('seo_title')
            ->add('sort_order')
            ->add('created_at_formatted', function (Category $category) {
                if ($category->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($category->created_at)->format('Y/m/d H:i');
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

    public function actions(Category $row): array
    {
        return [
            Button::add('edit')
                ->slot('<x-lucide-pencil class="w-4 h-4" />')
                ->class('text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm p-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800')
                ->tooltip(__('general.edit'))
                ->dispatch('modal-open', [
                    'modal' => 'sale.catalog.category.edit',
                    'props' => ['categoryId' => $row->id],
                ]),

            Button::add('delete')
                ->slot('<x-lucide-trash-2 class="w-4 h-4" />')
                ->class('text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm p-2 dark:bg-red-600 dark:hover:bg-red-700 focus:outline-none dark:focus:ring-red-800')
                ->tooltip(__('general.delete'))
                ->dispatch('modal-open', [
                    'modal' => 'sale.catalog.category.delete',
                    'props' => ['categoryId' => $row->id],
                ]),
        ];
    }
}
