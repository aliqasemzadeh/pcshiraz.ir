<?php

use App\Models\InstallmentPlan;
use App\Models\Organization;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int $installmentPlanId;

    public string $planTitle = '';

    /** @var list<int> */
    public array $selectedOrganizationIds = [];

    public function mount(int $installmentPlanId): void
    {
        $plan = InstallmentPlan::query()->with('organizations')->findOrFail($installmentPlanId);

        $this->installmentPlanId = $installmentPlanId;
        $this->planTitle = $plan->title;
        $this->selectedOrganizationIds = $plan->organizations->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    #[Computed]
    public function organizations()
    {
        return Organization::query()->where('is_active', true)->orderBy('code')->get(['id', 'code']);
    }

    public function save(): void
    {
        $plan = InstallmentPlan::query()->findOrFail($this->installmentPlanId);

        $sync = [];
        foreach (array_map('intval', $this->selectedOrganizationIds) as $organizationId) {
            $sync[$organizationId] = [
                'is_default' => false,
                'is_active' => true,
                'priority' => $plan->priority,
            ];
        }

        $plan->organizations()->sync($sync);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleInstallmentPlansTable');
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-2 text-xl font-semibold text-heading">
            {{ __('general.assign_organizations') }}
        </h2>
        <p class="mb-4 text-sm text-body">{{ $planTitle }}</p>

        <form wire:submit="save" class="space-y-4">
            <div x-data="{ search: '' }" class="space-y-2">
                <x-fwb.input type="search" size="sm" x-model="search" :placeholder="__('general.search')" />

                <div class="max-h-[60vh] space-y-2 overflow-y-auto rounded-lg border border-default p-3">
                    @forelse ($this->organizations as $organization)
                        <div
                            x-show="!search || $el.dataset.search.includes(search.toLowerCase())"
                            data-search="{{ mb_strtolower($organization->code) }}"
                        >
                            <x-fwb.checkbox
                                :id="'plan-org-'.$organization->id"
                                wire:model="selectedOrganizationIds"
                                :value="$organization->id"
                                :label="$organization->code"
                            />
                        </div>
                    @empty
                        <p class="text-sm text-body">{{ __('general.no_organizations') }}</p>
                    @endforelse
                </div>
            </div>

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
