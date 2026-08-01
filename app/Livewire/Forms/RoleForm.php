<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class RoleForm extends Form
{
    public ?Role $role = null;

    public string $name = '';

    public string $guard_name = 'web';

    public function setRole(Role $role): void
    {
        $this->role = $role;
        $this->name = $role->name;
        $this->guard_name = $role->guard_name;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where(fn ($query) => $query->where('guard_name', $this->guard_name))
                    ->ignore($this->role?->id),
            ],
            'guard_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'name' => __('general.name'),
            'guard_name' => __('general.guard_name'),
        ];
    }

    public function store(): Role
    {
        $this->validate();

        return Role::query()->create([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);
    }

    public function update(Role $role): Role
    {
        $this->role = $role;
        $this->validate();

        $role->update([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);

        return $role->refresh();
    }
}
