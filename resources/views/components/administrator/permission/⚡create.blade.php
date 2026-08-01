<?php

use App\Livewire\Forms\PermissionForm;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public PermissionForm $form;

    public function save(): void
    {
        $this->form->store();

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-administratorPermissionsTable');
        $this->form->reset();
        $this->form->guard_name = 'web';
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.create_permission') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            <div>
                <x-fwb.input
                    wire:model="form.name"
                    :label="__('general.name')"
                    type="text"
                    dir="ltr"
                    placeholder="sale.category_view"
                />
                @error('form.name')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="form.guard_name"
                    :label="__('general.guard_name')"
                    type="text"
                    dir="ltr"
                />
                @error('form.guard_name')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
