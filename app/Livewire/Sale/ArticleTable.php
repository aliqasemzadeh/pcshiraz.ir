<?php

namespace App\Livewire\Sale;

use App\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Morilog\Jalali\Jalalian;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class ArticleTable extends PowerGridComponent
{
    public string $tableName = 'saleArticlesTable';

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
        return Article::query()
            ->with(['media', 'tags'])
            ->orderByDesc('id');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('image', function (Article $article) {
                $media = $article->getFirstMedia('article_image');

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
                    e($article->title),
                );
            })
            ->add('title')
            ->add('tags_label', function (Article $article) {
                if ($article->tags->isEmpty()) {
                    return '<span class="text-sm text-body">—</span>';
                }

                return $article->tags
                    ->map(fn ($tag) => '<span class="inline-block rounded-base bg-neutral-secondary-soft px-2 py-0.5 text-xs text-body me-1">'.e((string) $tag->name).'</span>')
                    ->implode('');
            })
            ->add('is_active_label', function (Article $article) {
                return $article->is_active
                    ? '<span class="text-sm text-green-600 dark:text-green-400">'.e(__('general.active')).'</span>'
                    : '<span class="text-sm text-body">'.e(__('general.inactive')).'</span>';
            })
            ->add('created_at_formatted', function (Article $article) {
                if ($article->created_at === null) {
                    return '—';
                }

                return Jalalian::fromDateTime($article->created_at)->format('Y/m/d H:i');
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

            Column::make(__('general.tags'), 'tags_label')
                ->visibleInExport(false),

            Column::make(__('general.active'), 'is_active_label', 'is_active')
                ->sortable(),

            Column::make(__('general.created_at'), 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action(__('general.actions')),
        ];
    }

    public function actionsFromView(Article $row): View
    {
        return view('components.powergrid.row-actions', [
            'row' => $row,
            'editModal' => 'sale.article.edit',
            'deleteModal' => 'sale.article.delete',
            'idProp' => 'articleId',
        ]);
    }
}
