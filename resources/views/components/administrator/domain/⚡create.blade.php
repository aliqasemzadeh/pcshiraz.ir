<?php

use App\Livewire\Forms\DomainForm;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public DomainForm $form;

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

    public function save(): void
    {
        $this->form->store();

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('pg:eventRefresh-administratorDomainsTable');
        $this->form->reset();
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('general.create_domain') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            <div>
                <x-fwb.select
                    wire:model="form.user_id"
                    :label="__('general.user')"
                    :placeholder="__('general.select_user')"
                    :options="$this->users"
                />
                @error('form.user_id')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="form.title"
                    :label="__('general.title')"
                    type="text"
                />
                @error('form.title')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.input
                    wire:model="form.domain"
                    :label="__('general.domain_name')"
                    type="text"
                    dir="ltr"
                />
                @error('form.domain')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-fwb.textarea
                    wire:model="form.description"
                    :label="__('general.description')"
                    rows="3"
                />
                @error('form.description')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
