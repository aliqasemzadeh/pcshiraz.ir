<?php

use App\Enums\OrganizationUserRoleEnum;
use App\Models\Organization;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int $organizationId;

    public string $organizationCode = '';

    /** @var list<int> */
    public array $selectedUserIds = [];

    public function mount(int $organizationId): void
    {
        $organization = Organization::query()->with('approvers')->findOrFail($organizationId);

        $this->organizationId = $organizationId;
        $this->organizationCode = $organization->code;
        $this->selectedUserIds = $organization->approvers->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->orderByDesc('id')
            ->limit(500)
            ->get(['id', 'mobile', 'first_name', 'last_name']);
    }

    public function save(): void
    {
        $organization = Organization::query()->findOrFail($this->organizationId);
        $selectedIds = array_map('intval', $this->selectedUserIds);

        $sync = [];
        foreach ($selectedIds as $userId) {
            $sync[$userId] = [
                'role' => OrganizationUserRoleEnum::Approver->value,
                'is_active' => true,
            ];
        }

        $organization->users()->sync($sync);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-administratorOrganizationsTable');
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-2 text-xl font-semibold text-heading">
            {{ __('general.assign_approvers') }}
        </h2>
        <p class="mb-4 text-sm text-body" dir="ltr">{{ $organizationCode }}</p>

        <form wire:submit="save" class="space-y-4">
            <div x-data="{ search: '' }" class="space-y-2">
                <x-fwb.input
                    type="search"
                    size="sm"
                    x-model="search"
                    :placeholder="__('general.search')"
                />

                <div class="max-h-[60vh] space-y-2 overflow-y-auto rounded-lg border border-default p-3">
                    @forelse ($this->users as $user)
                        @php
                            $label = trim($user->full_name) !== ''
                                ? $user->full_name.' ('.$user->mobile.')'
                                : $user->mobile;
                        @endphp
                        <div
                            x-show="!search || $el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ mb_strtolower($label) }}"
                        >
                            <x-fwb.checkbox
                                :id="'org-user-'.$user->id"
                                wire:model="selectedUserIds"
                                :value="$user->id"
                                :label="$label"
                            />
                        </div>
                    @empty
                        <p class="text-sm text-body">{{ __('general.no_users') }}</p>
                    @endforelse
                </div>
            </div>

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
