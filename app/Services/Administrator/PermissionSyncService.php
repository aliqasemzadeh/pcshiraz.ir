<?php

namespace App\Services\Administrator;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSyncService
{
    public const GUARD = 'web';

    /**
     * @return array{
     *     created: int,
     *     existing: int,
     *     assigned_to_roles: int,
     *     total: int,
     *     roles_created: int,
     *     roles_existing: int,
     *     permissions_synced: int,
     *     roles_total: int
     * }
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

        $roleResult = $assignToRoles
            ? $this->syncRoles($permissionNames)
            : [
                'roles_created' => 0,
                'roles_existing' => 0,
                'permissions_synced' => 0,
                'roles_total' => count($this->roleDefinitionsFromLang()),
            ];

        $assignedToRoles = $assignToRoles
            ? $this->assignNewPermissionsToCustomRoles($permissionNames, array_keys($this->roleDefinitionsFromLang()))
            : 0;

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            'created' => $created,
            'existing' => $existing,
            'assigned_to_roles' => $assignedToRoles,
            'total' => count($permissionNames),
            ...$roleResult,
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
     * @return array<string, array{label: string, permissions: list<string>}>
     */
    public function roleDefinitionsFromLang(): array
    {
        /** @var array<string, array{label?: string, permissions?: list<string>}> $roles */
        $roles = trans('roles');

        if (! is_array($roles)) {
            return [];
        }

        $definitions = [];

        foreach ($roles as $name => $definition) {
            if (! is_array($definition)) {
                continue;
            }

            $permissions = $definition['permissions'] ?? [];

            if (! is_array($permissions)) {
                $permissions = [];
            }

            $definitions[$name] = [
                'label' => (string) ($definition['label'] ?? $name),
                'permissions' => array_values($permissions),
            ];
        }

        return $definitions;
    }

    /**
     * @param  list<string>  $permissionNames
     * @return array{roles_created: int, roles_existing: int, permissions_synced: int, roles_total: int}
     */
    protected function syncRoles(array $permissionNames): array
    {
        $definitions = $this->roleDefinitionsFromLang();
        $rolesCreated = 0;
        $rolesExisting = 0;
        $permissionsSynced = 0;

        foreach ($definitions as $roleName => $definition) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', self::GUARD)
                ->first();

            if ($role === null) {
                $role = Role::findOrCreate($roleName, self::GUARD);
                $rolesCreated++;
            } else {
                $rolesExisting++;
            }

            $resolvedPermissions = $this->resolveRolePermissions(
                $definition['permissions'],
                $permissionNames,
            );

            $role->syncPermissions($resolvedPermissions);
            $permissionsSynced += count($resolvedPermissions);
        }

        return [
            'roles_created' => $rolesCreated,
            'roles_existing' => $rolesExisting,
            'permissions_synced' => $permissionsSynced,
            'roles_total' => count($definitions),
        ];
    }

    /**
     * @param  list<string>  $patterns
     * @param  list<string>  $allPermissionNames
     * @return list<string>
     */
    protected function resolveRolePermissions(array $patterns, array $allPermissionNames): array
    {
        if (in_array('*', $patterns, true)) {
            return $allPermissionNames;
        }

        $resolved = [];

        foreach ($patterns as $pattern) {
            if (str_ends_with($pattern, '.*')) {
                $prefix = substr($pattern, 0, -1);

                foreach ($allPermissionNames as $name) {
                    if (str_starts_with($name, $prefix)) {
                        $resolved[] = $name;
                    }
                }

                continue;
            }

            if (in_array($pattern, $allPermissionNames, true)) {
                $resolved[] = $pattern;
            }
        }

        sort($resolved);

        return array_values(array_unique($resolved));
    }

    /**
     * @param  list<string>  $permissionNames
     * @param  list<string>  $predefinedRoleNames
     */
    protected function assignNewPermissionsToCustomRoles(array $permissionNames, array $predefinedRoleNames): int
    {
        $permissionsByModule = [];

        foreach ($permissionNames as $name) {
            $module = strstr($name, '.', true) ?: $name;
            $permissionsByModule[$module][] = $name;
        }

        $assigned = 0;

        Role::query()
            ->with('permissions')
            ->whereNotIn('name', $predefinedRoleNames)
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
