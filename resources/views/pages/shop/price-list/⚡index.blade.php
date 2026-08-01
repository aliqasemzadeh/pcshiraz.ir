<?php

use App\Models\Category;
use App\Services\Shop\PriceListService;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Morilog\Jalali\Jalalian;
use Symfony\Component\HttpFoundation\StreamedResponse;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public ?Category $category = null;

    #[Url]
    public ?int $brand = null;

    #[Url]
    public string $q = '';

    public bool $chartOpen = false;

    public ?int $chartItemId = null;

    public string $chartItemTitle = '';

    /** @var list<array{label: string, value: float}> */
    public array $chartPoints = [];

    public function mount(?Category $category = null): void
    {
        $this->category = $category;
    }

    public function updatedBrand(): void
    {
        $this->resetPage();
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function selectCategory(?int $categoryId): void
    {
        if ($categoryId === null) {
            $this->redirect(route('shop.price-list'), navigate: true);

            return;
        }

        $category = Category::query()->findOrFail($categoryId);
        $this->redirect(route('shop.price-list', $category), navigate: true);
    }

    public function openChart(int $itemId): void
    {
        $service = app(PriceListService::class);
        $item = $service->query($this->category?->id)
            ->where('items.id', $itemId)
            ->firstOrFail();

        $this->chartItemId = $item->id;
        $this->chartItemTitle = $item->title;
        $this->chartPoints = $service->priceHistory($item->id);
        $this->chartOpen = true;

        $this->dispatch('price-chart-updated', points: $this->chartPoints, title: $this->chartItemTitle);
    }

    public function closeChart(): void
    {
        $this->chartOpen = false;
        $this->chartItemId = null;
        $this->chartItemTitle = '';
        $this->chartPoints = [];
    }

    public function exportPdf(): mixed
    {
        if ($this->category === null) {
            return null;
        }

        $service = app(PriceListService::class);
        $items = $service->all($this->category->id, $this->brand, $this->q !== '' ? $this->q : null);
        $brandTitle = null;

        if ($this->brand !== null) {
            $brandTitle = collect($service->brandOptions($this->category->id))
                ->firstWhere('id', $this->brand)['title'] ?? null;
        }

        $pdf = Pdf::loadView('shop.price-list.pdf', [
            'category' => $this->category,
            'items' => $items,
            'brandTitle' => $brandTitle,
            'exportedAt' => Jalalian::now()->format('Y/m/d H:i'),
        ])->setPaper('a4', 'portrait');

        $filename = 'price-list-'.$this->category->slug.'-'.Jalalian::now()->format('Y-m-d').'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    #[Computed]
    public function categoryOptions(): array
    {
        $options = ['' => __('general.select_category')];

        foreach (app(PriceListService::class)->categories() as $category) {
            $options[$category->id] = $category->title;
        }

        return $options;
    }

    #[Computed]
    public function brandOptions(): array
    {
        $options = ['' => __('general.all_brands')];

        foreach (app(PriceListService::class)->brandOptions($this->category?->id) as $brand) {
            $options[$brand['id']] = $brand['title'];
        }

        return $options;
    }

    #[Computed]
    public function items()
    {
        if ($this->category === null) {
            return null;
        }

        return app(PriceListService::class)->paginate(
            $this->category->id,
            $this->brand,
            $this->q !== '' ? $this->q : null,
        );
    }
};
?>

<div
    class="space-y-6"
    x-data="priceChartModal()"
    @price-chart-updated.window="renderChart($event.detail.points || [], $event.detail.title || '')"
>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-ink">{{ __('general.price_list') }}</h1>
            <p class="mt-1 text-sm text-navbar-fg">
                @if ($category)
                    {{ $category->title }}
                @else
                    {{ __('general.select_category_for_price_list') }}
                @endif
            </p>
        </div>

        @if ($category)
            <button
                type="button"
                wire:click="exportPdf"
                class="inline-flex items-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-strong focus:ring-4 focus:ring-brand/30 focus:outline-none"
            >
                <x-lucide-download class="h-4 w-4" />
                {{ __('general.export_price_list') }}
            </button>
        @endif
    </div>

    <div class="grid gap-3 rounded-xl border border-nav-border bg-surface p-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <x-fwb.select
                wire:change="selectCategory($event.target.value || null)"
                :label="__('general.category')"
                :options="$this->categoryOptions"
                :value="$category?->id"
            />
        </div>

        @if ($category)
            <div>
                <x-fwb.select
                    wire:model.live="brand"
                    :label="__('app.filter_brand')"
                    :options="$this->brandOptions"
                />
            </div>
            <div>
                <x-fwb.input
                    wire:model.live.debounce.300ms="q"
                    :label="__('general.search')"
                    type="search"
                    :placeholder="__('general.search_items')"
                />
            </div>
        @endif
    </div>

    @if (! $category)
        <div class="rounded-xl border border-dashed border-nav-border p-10 text-center text-navbar-fg">
            {{ __('general.select_category_for_price_list') }}
        </div>
    @elseif ($this->items->isEmpty())
        <div class="rounded-xl border border-dashed border-nav-border p-10 text-center text-navbar-fg">
            {{ __('app.no_products') }}
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-nav-border bg-surface">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b border-nav-border bg-canvas text-start text-navbar-fg">
                        <tr>
                            <th class="px-4 py-3 font-medium">{{ __('general.title') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('general.brand') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('general.color') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('general.price') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->items as $item)
                            <tr class="border-b border-nav-border last:border-0 hover:bg-brand-softer/40" wire:key="price-row-{{ $item->id }}">
                                <td class="px-4 py-3">
                                    <a href="{{ route('shop.item', $item) }}" wire:navigate class="font-medium text-ink hover:text-brand">
                                        {{ $item->title }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-navbar-fg">{{ $item->brand?->title ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if ($item->color_code)
                                            <span class="inline-block h-3.5 w-3.5 rounded-full border border-nav-border" style="background-color: {{ $item->color_code }}"></span>
                                        @endif
                                        <span>{{ $item->color_name ?: '—' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-semibold text-brand" dir="ltr">
                                    @if ($item->is_contact_price)
                                        <span class="text-amber-600 dark:text-amber-400">{{ __('general.contact_price') }}</span>
                                    @elseif ($item->activeCashPrice)
                                        {{ number_format((float) $item->activeCashPrice->sale_price) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <button
                                        type="button"
                                        wire:click="openChart({{ $item->id }})"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-nav-border px-2.5 py-1.5 text-xs font-medium text-ink hover:border-brand hover:bg-brand-softer hover:text-brand"
                                        title="{{ __('general.price_chart') }}"
                                    >
                                        <x-lucide-chart-line class="h-4 w-4" />
                                        <span class="hidden sm:inline">{{ __('general.price_chart') }}</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $this->items->links() }}
        </div>
    @endif

    <div
        x-cloak
        x-show="open || $wire.chartOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('general.price_chart') }}"
    >
        <div
            class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"
            @click="close(); $wire.closeChart()"
            x-show="open || $wire.chartOpen"
            x-transition.opacity
        ></div>

        <div
            class="relative z-10 w-full max-w-2xl rounded-2xl border border-nav-border bg-surface p-5 shadow-xl"
            x-show="open || $wire.chartOpen"
            x-transition
            @click.stop
        >
            <div class="mb-4 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold text-ink">{{ __('general.price_chart') }}</h2>
                    <p class="truncate text-sm text-navbar-fg" x-text="title || @js($chartItemTitle)"></p>
                </div>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full text-navbar-fg hover:bg-brand-softer hover:text-ink"
                    @click="close(); $wire.closeChart()"
                    aria-label="{{ __('general.close') }}"
                >
                    <x-lucide-x class="h-5 w-5" />
                </button>
            </div>

            <div class="relative h-72 w-full">
                <canvas x-ref="canvas"></canvas>
                <p
                    x-show="!hasData"
                    class="absolute inset-0 flex items-center justify-center text-sm text-navbar-fg"
                >
                    {{ __('general.no_price_history') }}
                </p>
            </div>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('priceChartModal', () => ({
        open: false,
        title: '',
        hasData: false,
        chart: null,

        init() {
            this.$watch('$wire.chartOpen', (value) => {
                this.open = value;
                if (!value) {
                    this.destroyChart();
                }
            });
        },

        close() {
            this.open = false;
            this.destroyChart();
        },

        destroyChart() {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
            this.hasData = false;
        },

        async renderChart(points, title) {
            this.open = true;
            this.title = title || '';
            this.destroyChart();

            const labels = (points || []).map((p) => p.label);
            const values = (points || []).map((p) => p.value);
            this.hasData = values.length > 0;

            if (!this.hasData) {
                return;
            }

            const { Chart, registerables } = await import('chart.js');
            Chart.register(...registerables);

            await this.$nextTick();

            this.chart = new Chart(this.$refs.canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: @js(__('general.sale_price')),
                        data: values,
                        borderColor: '#0f766e',
                        backgroundColor: 'rgba(15, 118, 110, 0.12)',
                        tension: 0.25,
                        fill: true,
                        pointRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: (value) => Number(value).toLocaleString(),
                            },
                        },
                    },
                },
            });
        },
    }));
</script>
@endscript
