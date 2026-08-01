import 'flowbite';
import { initFlowbite } from 'flowbite';
import './../../vendor/power-components/livewire-powergrid/dist/powergrid';
import '../../vendor/masmerise/livewire-toaster/resources/js';

const bootFlowbite = () => initFlowbite();

document.addEventListener('DOMContentLoaded', bootFlowbite);
document.addEventListener('livewire:navigated', bootFlowbite);
window.addEventListener('pg-livewire-request-finished', bootFlowbite);
document.addEventListener('livewire:morph.updated', bootFlowbite);

document.addEventListener('alpine:init', () => {
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

document.addEventListener('livewire:navigated', () => {
    if (window.Alpine?.store('ui')) {
        Alpine.store('ui').reset();
    }
});
