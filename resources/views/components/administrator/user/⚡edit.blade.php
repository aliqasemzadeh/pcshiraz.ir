<?php

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public UserForm $form;

    public int $userId;

    public function mount(int $userId): void
    {
        $this->userId = $userId;
        $this->form->setUser(User::query()->findOrFail($userId));
    }

    public function save(): void
    {
        $user = User::query()->findOrFail($this->userId);
        $this->form->update($user);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-administratorUsersTable');
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.edit_user') }}
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

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
