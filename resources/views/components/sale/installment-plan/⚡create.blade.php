<?php

use App\Livewire\Forms\InstallmentPlanForm;
use App\Models\Organization;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public InstallmentPlanForm $form;

    #[Computed]
    public function organizations()
    {
        return Organization::query()->orderBy('code')->get(['id', 'code']);
    }

    public function save(): void
    {
        $this->form->store();

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-saleInstallmentPlansTable');
        $this->form->reset();
        $this->form->term_months = 10;
        $this->form->is_active = true;
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-lg overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.create_installment_plan') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            @include('components.sale.installment-plan.partials.form-fields', [
                'form' => $form,
                'organizations' => $this->organizations,
                'checkboxId' => 'create',
            ])

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
