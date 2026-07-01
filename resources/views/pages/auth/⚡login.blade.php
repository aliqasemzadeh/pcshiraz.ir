<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::auth')] class extends Component
{
    //
};
?>

<form class="space-y-6">
    <flux:input type="number" label="Email" />
    <flux:button variant="primary" class="w-full">Send invite</flux:button>
</form>
