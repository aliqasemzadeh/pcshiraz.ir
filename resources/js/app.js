import 'flowbite';
import { initFlowbite } from 'flowbite';
import smoothscroll from 'smoothscroll-polyfill';
import { Chart, registerables } from 'chart.js';
import './../../vendor/power-components/livewire-powergrid/dist/powergrid';
import '../../vendor/masmerise/livewire-toaster/resources/js';

Chart.register(...registerables);
window.Chart = Chart;

smoothscroll.polyfill();

const bootFlowbite = () => initFlowbite();

document.addEventListener('DOMContentLoaded', bootFlowbite);
document.addEventListener('livewire:navigated', bootFlowbite);
window.addEventListener('pg-livewire-request-finished', bootFlowbite);
document.addEventListener('livewire:morph.updated', bootFlowbite);

document.addEventListener('alpine:init', () => {
    Alpine.directive('modal', (el, { value, modifiers, expression }, { evaluate, cleanup }) => {
        const prevent = modifiers?.includes('prevent');
        const stop = modifiers?.includes('stop');
        const params = expression ? evaluate(expression) : undefined;

        if (value === 'open') {
            const preload = modifiers?.includes('preload');

            const onClick = (e) => {
                if (prevent) e.preventDefault();
                if (stop) e.stopImmediatePropagation();
                Livewire.dispatch('modal-open', params);
            };

            const onMouseenter = () => {
                Livewire.dispatch('modal-preload', params);
            };

            el.addEventListener('click', onClick, { capture: true });

            if (preload) {
                el.addEventListener('mouseenter', onMouseenter, { capture: true });
            }

            cleanup(() => {
                el.removeEventListener('click', onClick, { capture: true });
                if (preload) {
                    el.removeEventListener('mouseenter', onMouseenter, { capture: true });
                }
            });
        } else if (value === 'close') {
            const onClick = (e) => {
                if (prevent) e.preventDefault();
                if (stop) e.stopImmediatePropagation();
                Livewire.dispatch('modal-close', params);
            };

            el.addEventListener('click', onClick, { capture: true });
            cleanup(() => el.removeEventListener('click', onClick, { capture: true }));
        } else if (value === 'close-all') {
            const onClick = (e) => {
                if (prevent) e.preventDefault();
                if (stop) e.stopImmediatePropagation();
                Livewire.dispatch('modal-close-all', params);
            };

            el.addEventListener('click', onClick, { capture: true });
            cleanup(() => el.removeEventListener('click', onClick, { capture: true }));
        }
    });

    Alpine.data('priceChartModal', () => ({
        open: false,
        title: '',
        hasData: false,
        chart: null,

        init() {
            this.$watch('$wire.chartOpen', (value) => {
                this.open = !!value;
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

        renderChart(points, title) {
            this.open = true;
            this.title = title || '';
            this.destroyChart();

            const labels = (points || []).map((p) => p.label);
            const values = (points || []).map((p) => p.value);
            this.hasData = values.length > 0;

            if (!this.hasData || !window.Chart || !this.$refs.canvas) {
                return;
            }

            this.$nextTick(() => {
                this.chart = new window.Chart(this.$refs.canvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'sale_price',
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
            });
        },
    }));

    Alpine.store('ui', {
        busy: false,
        busyCount: 0,
        start() {
            this.busyCount++;
            this.busy = true;
        },
        end() {
            this.busyCount = Math.max(0, this.busyCount - 1);
            this.busy = this.busyCount > 0;
        },
        reset() {
            this.busyCount = 0;
            this.busy = false;
        },
    });
});

document.addEventListener('livewire:init', () => {
    if (!Alpine.store('ui')) {
        Alpine.store('ui', {
            busy: false,
            busyCount: 0,
            start() {
                this.busyCount++;
                this.busy = true;
            },
            end() {
                this.busyCount = Math.max(0, this.busyCount - 1);
                this.busy = this.busyCount > 0;
            },
            reset() {
                this.busyCount = 0;
                this.busy = false;
            },
        });
    }
});

document.addEventListener('livewire:navigated', () => {
    if (window.Alpine?.store('ui')) {
        Alpine.store('ui').reset();
    }
});
