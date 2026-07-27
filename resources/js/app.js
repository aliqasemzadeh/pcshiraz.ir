import 'flowbite';
import { initFlowbite } from 'flowbite';
import './../../vendor/power-components/livewire-powergrid/dist/powergrid';
import '../../vendor/masmerise/livewire-toaster/resources/js';

document.addEventListener('DOMContentLoaded', () => {
    initFlowbite();
});

document.addEventListener('livewire:navigated', () => {
    initFlowbite();
});
