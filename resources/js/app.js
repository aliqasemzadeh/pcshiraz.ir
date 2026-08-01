import 'flowbite';
import { initFlowbite } from 'flowbite';
import './../../vendor/power-components/livewire-powergrid/dist/powergrid';
import '../../vendor/masmerise/livewire-toaster/resources/js';

const bootFlowbite = () => initFlowbite();

document.addEventListener('DOMContentLoaded', bootFlowbite);
document.addEventListener('livewire:navigated', bootFlowbite);
window.addEventListener('pg-livewire-request-finished', bootFlowbite);
document.addEventListener('livewire:morph.updated', bootFlowbite);
