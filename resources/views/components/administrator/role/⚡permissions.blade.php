<?php

use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public int $roleId;

    public string $roleName = '';

    /** @var list<int> */
    public array $selectedPermissionIds = [];

    public function mount(int $roleId): void
    {
        $role = Role::query()->with('permissions')->findOrFail($roleId);

        $this->roleId = $roleId;
        $this->roleName = $role->name;
        $this->selectedPermissionIds = $role->permissions->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    #[Computed]
    public function permissions()
    {
        return Permission::query()->orderBy('name')->get(['id', 'name', 'guard_name']);
    }

    public function save(): void
    {
        $role = Role::query()->findOrFail($this->roleId);

        $permissionNames = Permission::query()
            ->whereIn('id', $this->selectedPermissionIds)
            ->pluck('name')
            ->all();

        $role->syncPermissions($permissionNames);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-administratorRolesTable');
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-lg overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-2 text-xl font-semibold text-heading">
            {{ __('general.assign_permissions') }}
        </h2>
        <p class="mb-4 text-sm text-body" dir="ltr">{{ $roleName }}</p>

        <form wire:submit="save" class="space-y-4">
            <div class="max-h-[60vh] space-y-2 overflow-y-auto rounded-lg border border-default p-3">
                @forelse ($this->permissions as $permission)
                    @php
                        $labelKey = 'permissions.'.$permission->name;
                        $translated = __($labelKey);
                        $label = $translated !== $labelKey
                            ? $translated.' ('.$permission->name.')'
                            : $permission->name.' ('.$permission->guard_name.')';
                    @endphp
                    <x-fwb.checkbox
                        wire:model="selectedPermissionIds"
                        :value="$permission->id"
                        :label="$label"
                    />
                @empty
                    <p class="text-sm text-body">{{ __('general.no_permissions') }}</p>
                @endforelse
            </div>

            @error('selectedPermissionIds')
                <p class="text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
