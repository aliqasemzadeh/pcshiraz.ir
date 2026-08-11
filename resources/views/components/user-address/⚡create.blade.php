<?php

use App\Livewire\Concerns\InteractsWithIranLocations;
use App\Livewire\Forms\UserAddressForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    use InteractsWithIranLocations;

    public UserAddressForm $form;

    public function save(): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $this->form->store($user);

        Toaster::success(__('general.saved'));
        $this->dispatch('modal-close');
        $this->dispatch('shop.profile.address.saved');
        $this->form->resetForCreate();
        unset($this->cities);
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="w-full max-w-md overflow-auto bg-white p-5 dark:bg-gray-800"
    >
        <h2 class="mb-4 text-xl font-semibold text-heading">
            {{ __('app.create_address') }}
        </h2>

        <form wire:submit="save" class="space-y-4">
            @include('components.user-address.partials.form-fields', [
                'provinces' => $this->provinces,
                'cities' => $this->cities,
                'form' => $form,
            ])

            <x-ui.button type="submit" color="green" target="save" class="w-full">
                {{ __('general.save') }}
            </x-ui.button>
        </form>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
