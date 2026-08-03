<?php

use App\Livewire\Forms\OrganizationForm;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public OrganizationForm $form;

    public function save(): void
    {
        $this->form->store();

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-administratorOrganizationsTable');
        $this->form->reset();
        $this->form->is_active = true;
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.create_organization') }}
        </h2>

        <p class="mb-4 text-sm text-body">
            {{ __('general.organization_code_auto_generated') }}
        </p>

        <form wire:submit="save" class="space-y-4">
            <div>
                <x-fwb.textarea
                    wire:model="form.internal_note"
                    :label="__('general.internal_note')"
                    rows="3"
                />
                @error('form.internal_note')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.checkbox
                    id="org-create-active"
                    wire:model="form.is_active"
                    :label="__('general.active')"
                />
            </div>

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
