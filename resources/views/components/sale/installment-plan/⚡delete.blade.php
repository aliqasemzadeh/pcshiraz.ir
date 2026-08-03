<?php

use App\Models\InstallmentPlan;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public int $installmentPlanId;

    public string $planTitle = '';

    public function mount(int $installmentPlanId): void
    {
        $this->installmentPlanId = $installmentPlanId;
        $plan = InstallmentPlan::query()->findOrFail($installmentPlanId);
        $this->planTitle = $plan->title;
    }

    public function delete(): void
    {
        InstallmentPlan::query()->findOrFail($this->installmentPlanId)->delete();

        Toaster::success(__('general.deleted'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleInstallmentPlansTable');
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
        <p class="mb-2 text-sm text-body">{{ $planTitle }}</p>
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
