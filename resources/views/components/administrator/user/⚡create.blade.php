<?php

use App\Livewire\Forms\UserForm;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public UserForm $form;

    public function save(): void
    {
        $this->form->store();

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-administratorUsersTable');
        $this->form->reset();
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.create_user') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            <div>
                <x-fwb.input
                    wire:model="form.mobile"
                    :label="__('general.mobile')"
                    type="text"
                    dir="ltr"
                    inputmode="numeric"
                />
                @error('form.mobile')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input wire:model="form.first_name" :label="__('general.first_name')" type="text" />
                @error('form.first_name')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input wire:model="form.last_name" :label="__('general.last_name')" type="text" />
                @error('form.last_name')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input wire:model="form.national_code" :label="__('general.national_code')" type="text" dir="ltr" />
                @error('form.national_code')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-ui.jalali-datepicker wire:model="form.birth_date" :label="__('general.birth_date')" max-date="today" />
                @error('form.birth_date')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
