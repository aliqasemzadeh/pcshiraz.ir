<?php

namespace App\Livewire\Forms;

use App\Models\Organization;
use Livewire\Form;

class OrganizationForm extends Form
{
    public ?Organization $organization = null;

    public ?string $internal_note = null;

    public bool $is_active = true;

    public function setOrganization(Organization $organization): void
    {
        $this->organization = $organization;
        $this->internal_note = $organization->internal_note;
        $this->is_active = $organization->is_active;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'internal_note' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'internal_note' => __('general.internal_note'),
            'is_active' => __('general.active'),
        ];
    }

    public function store(): Organization
    {
        $this->validate();

        return Organization::query()->create([
            'code' => Organization::generateCode(),
            'internal_note' => $this->internal_note,
            'is_active' => $this->is_active,
        ]);
    }

    public function update(Organization $organization): Organization
    {
        $this->organization = $organization;
        $this->validate();

        $organization->update([
            'internal_note' => $this->internal_note,
            'is_active' => $this->is_active,
        ]);

        return $organization->refresh();
    }
}
