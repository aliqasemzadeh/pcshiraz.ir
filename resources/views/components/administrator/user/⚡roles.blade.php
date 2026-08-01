<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public int $userId;

    public string $userLabel = '';

    /** @var list<int> */
    public array $selectedRoleIds = [];

    public function mount(int $userId): void
    {
        $user = User::query()->with('roles')->findOrFail($userId);

        $this->userId = $userId;
        $this->userLabel = $user->full_name !== ''
            ? $user->full_name.' ('.$user->mobile.')'
            : $user->mobile;
        $this->selectedRoleIds = $user->roles->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    #[Computed]
    public function roles()
    {
        return Role::query()->orderBy('name')->get(['id', 'name', 'guard_name']);
    }

    public function save(): void
    {
        $user = User::query()->findOrFail($this->userId);

        $roleNames = Role::query()
            ->whereIn('id', $this->selectedRoleIds)
            ->pluck('name')
            ->all();

        $user->syncRoles($roleNames);

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
        <h2 class="mb-2 text-xl font-semibold text-heading">
            {{ __('general.assign_roles') }}
        </h2>
        <p class="mb-4 text-sm text-body" dir="ltr">{{ $userLabel }}</p>

        <form wire:submit="save" class="space-y-4">
            <div class="max-h-[60vh] space-y-2 overflow-y-auto rounded-lg border border-default p-3">
                @forelse ($this->roles as $role)
                    <x-fwb.checkbox
                        wire:model="selectedRoleIds"
                        :value="$role->id"
                        :label="$role->name.' ('.$role->guard_name.')'"
                    />
                @empty
                    <p class="text-sm text-body">{{ __('general.no_roles') }}</p>
                @endforelse
            </div>

            @error('selectedRoleIds')
                <p class="text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
