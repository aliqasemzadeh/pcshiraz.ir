<?php

namespace App\Services\Administrator;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSyncService
{
    public const GUARD = 'web';

    /**
     * @return array{created: int, existing: int, assigned_to_roles: int, total: int}
     */
    public function sync(bool $assignToRoles = true): array
    {
        $permissionNames = $this->permissionNamesFromLang();
        $created = 0;
        $existing = 0;

        foreach ($permissionNames as $name) {
            $permission = Permission::query()
                ->where('name', $name)
                ->where('guard_name', self::GUARD)
                ->first();

            if ($permission === null) {
                Permission::findOrCreate($name, self::GUARD);
                $created++;
            } else {
                $existing++;
            }
        }

        $assignedToRoles = $assignToRoles
            ? $this->assignNewPermissionsToRoles($permissionNames)
            : 0;

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            'created' => $created,
            'existing' => $existing,
            'assigned_to_roles' => $assignedToRoles,
            'total' => count($permissionNames),
        ];
    }

    /**
     * @return list<string>
     */
    public function permissionNamesFromLang(): array
    {
        /** @var array<string, array<string, string>> $permissions */
        $permissions = trans('permissions');

        if (! is_array($permissions)) {
            return [];
        }

        $names = [];

        foreach ($permissions as $module => $actions) {
            if (! is_array($actions)) {
                continue;
            }

            foreach (array_keys($actions) as $action) {
                $names[] = "{$module}.{$action}";
            }
        }

        sort($names);

        return $names;
    }

    /**
     * @param  list<string>  $permissionNames
     */
    protected function assignNewPermissionsToRoles(array $permissionNames): int
    {
        $permissionsByModule = [];

        foreach ($permissionNames as $name) {
            $module = strstr($name, '.', true) ?: $name;
            $permissionsByModule[$module][] = $name;
        }

        $assigned = 0;

        Role::query()
            ->with('permissions')
            ->each(function (Role $role) use ($permissionsByModule, &$assigned): void {
                $rolePermissionNames = $role->permissions->pluck('name')->all();

                if ($rolePermissionNames === []) {
                    return;
                }

                $modules = collect($rolePermissionNames)
                    ->map(fn (string $name) => strstr($name, '.', true) ?: $name)
                    ->unique()
                    ->all();

                $toAssign = [];

                foreach ($modules as $module) {
                    foreach ($permissionsByModule[$module] ?? [] as $permissionName) {
                        if (! in_array($permissionName, $rolePermissionNames, true)) {
                            $toAssign[] = $permissionName;
                        }
                    }
                }

                if ($toAssign === []) {
                    return;
                }

                $role->givePermissionTo($toAssign);
                $assigned += count($toAssign);
            });

        return $assigned;
    }
}
