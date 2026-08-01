<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rule;
use Livewire\Form;
use Spatie\Permission\Models\Permission;

class PermissionForm extends Form
{
    public ?Permission $permission = null;

    public string $name = '';

    public string $guard_name = 'web';

    public function setPermission(Permission $permission): void
    {
        $this->permission = $permission;
        $this->name = $permission->name;
        $this->guard_name = $permission->guard_name;
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
                Rule::unique('permissions', 'name')
                    ->where(fn ($query) => $query->where('guard_name', $this->guard_name))
                    ->ignore($this->permission?->id),
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

    public function store(): Permission
    {
        $this->validate();

        return Permission::query()->create([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);
    }

    public function update(Permission $permission): Permission
    {
        $this->permission = $permission;
        $this->validate();

        $permission->update([
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ]);

        return $permission->refresh();
    }
}
