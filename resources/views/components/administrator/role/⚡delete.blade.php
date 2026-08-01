<?php

use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public int $roleId;

    public string $roleName = '';

    public function mount(int $roleId): void
    {
        $this->roleId = $roleId;
        $this->roleName = Role::query()->findOrFail($roleId)->name;
    }

    public function delete(): void
    {
        Role::query()->findOrFail($this->roleId)->delete();

        Toaster::success(__('general.deleted'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-administratorRolesTable');
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::modal
        position="center"
        class="w-full max-w-md overflow-auto rounded-lg bg-white p-5 dark:bg-gray-800"
    >
        <h3 class="mb-2 text-lg font-semibold text-heading">
            {{ __('general.delete_confirmation') }}
        </h3>
        <p class="mb-2 text-sm text-body" dir="ltr">
            {{ $roleName }}
        </p>
        <p class="mb-6 text-sm text-body">
            {{ __('general.delete_warning_message') }}
            <br>
            {{ __('general.action_cannot_be_reversed') }}
        </p>
        <div class="flex justify-end gap-2">
            <x-ui.button type="button" color="light" outline :loading="false" x-modal:close>
                {{ __('general.cancel') }}
            </x-ui.button>
            <x-ui.button type="button" color="red" target="delete" wire:click="delete">
                {{ __('general.delete') }}
            </x-ui.button>
        </div>
    </x-livewire-modal::modal>
</x-livewire-modal::stack>
