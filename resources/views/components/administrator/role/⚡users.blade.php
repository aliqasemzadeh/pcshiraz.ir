<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public int $roleId;

    public string $roleName = '';

    /** @var list<int> */
    public array $selectedUserIds = [];

    public function mount(int $roleId): void
    {
        $role = Role::query()->with('users')->findOrFail($roleId);

        $this->roleId = $roleId;
        $this->roleName = $role->name;
        $this->selectedUserIds = $role->users->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->orderByDesc('id')
            ->get(['id', 'mobile', 'first_name', 'last_name']);
    }

    public function save(): void
    {
        $role = Role::query()->findOrFail($this->roleId);
        $currentIds = $role->users()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $selectedIds = array_map('intval', $this->selectedUserIds);

        $toAttach = array_diff($selectedIds, $currentIds);
        $toDetach = array_diff($currentIds, $selectedIds);

        if ($toAttach !== []) {
            User::query()
                ->whereIn('id', $toAttach)
                ->get()
                ->each(fn (User $user) => $user->assignRole($role));
        }

        if ($toDetach !== []) {
            User::query()
                ->whereIn('id', $toDetach)
                ->get()
                ->each(fn (User $user) => $user->removeRole($role));
        }

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
            {{ __('general.assign_users') }}
        </h2>
        <p class="mb-4 text-sm text-body" dir="ltr">{{ $roleName }}</p>

        <form wire:submit="save" class="space-y-4">
            <div class="max-h-[60vh] space-y-2 overflow-y-auto rounded-lg border border-default p-3">
                @forelse ($this->users as $user)
                    @php
                        $label = $user->full_name !== ''
                            ? $user->full_name.' ('.$user->mobile.')'
                            : $user->mobile;
                    @endphp
                    <x-fwb.checkbox
                        :id="'role-user-'.$user->id"
                        wire:model="selectedUserIds"
                        :value="$user->id"
                        :label="$label"
                    />
                @empty
                    <p class="text-sm text-body">{{ __('general.no_users') }}</p>
                @endforelse
            </div>

            @error('selectedUserIds')
                <p class="text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
