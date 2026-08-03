<?php

use App\Livewire\Forms\InstallmentPlanForm;
use App\Models\InstallmentPlan;
use App\Models\Organization;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public InstallmentPlanForm $form;

    public int $installmentPlanId;

    #[Computed]
    public function organizations()
    {
        return Organization::query()->orderBy('code')->get(['id', 'code']);
    }

    public function mount(int $installmentPlanId): void
    {
        $this->installmentPlanId = $installmentPlanId;
        $plan = InstallmentPlan::query()->findOrFail($installmentPlanId);
        $this->form->setInstallmentPlan($plan);
    }

    public function save(): void
    {
        $plan = InstallmentPlan::query()->findOrFail($this->installmentPlanId);
        $this->form->update($plan);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleInstallmentPlansTable');
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-lg overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.edit_installment_plan') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            @include('components.sale.installment-plan.partials.form-fields', [
                'form' => $form,
                'organizations' => $this->organizations,
                'checkboxId' => 'edit',
            ])

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
