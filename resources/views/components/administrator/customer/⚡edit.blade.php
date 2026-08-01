<?php

use App\Livewire\Forms\CustomerForm;
use App\Models\Customer;
use App\Models\Domain;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public CustomerForm $form;

    public int $customerId;

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function users(): array
    {
        return User::query()
            ->orderBy('mobile')
            ->pluck('mobile', 'id')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function domains(): array
    {
        return Domain::query()
            ->orderBy('title')
            ->get()
            ->mapWithKeys(fn (Domain $domain) => [
                $domain->id => $domain->title.' ('.$domain->domain.')',
            ])
            ->all();
    }

    public function mount(int $customerId): void
    {
        $this->customerId = $customerId;
        $this->form->setCustomer(Customer::query()->findOrFail($customerId));
    }

    public function save(): void
    {
        $customer = Customer::query()->findOrFail($this->customerId);
        $this->form->update($customer);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-administratorCustomersTable');
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.edit_customer') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            <div>
                <x-fwb.select
                    wire:model="form.user_id"
                    :label="__('general.user')"
                    :options="$this->users"
                />
                @error('form.user_id')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.select
                    wire:model="form.domain_id"
                    :label="__('general.domains')"
                    :options="$this->domains"
                />
                @error('form.domain_id')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="form.first_name"
                    :label="__('general.first_name')"
                    type="text"
                />
                @error('form.first_name')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="form.last_name"
                    :label="__('general.last_name')"
                    type="text"
                />
                @error('form.last_name')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="form.national_code"
                    :label="__('general.national_code')"
                    type="text"
                    dir="ltr"
                />
                @error('form.national_code')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="form.birth_date"
                    :label="__('general.birth_date')"
                    type="text"
                    dir="ltr"
                    placeholder="1370/01/01"
                />
                @error('form.birth_date')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
