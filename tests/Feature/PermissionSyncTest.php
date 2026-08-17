<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionSyncTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function sync_command_creates_permissions_from_lang_files(): void
    {
        $this->artisan('permissions:sync')
            ->assertSuccessful();

        $this->assertDatabaseHas('permissions', [
            'name' => 'sale.item_edit',
            'guard_name' => 'web',
        ]);

        $this->assertDatabaseHas('permissions', [
            'name' => 'organization.order_approve',
            'guard_name' => 'web',
        ]);
    }

    #[Test]
    public function sync_creates_default_roles_with_module_permissions(): void
    {
        $this->artisan('permissions:sync')
            ->assertSuccessful();

        $saleManager = Role::findByName('sale-manager', 'web');
        $administrator = Role::findByName('administrator', 'web');
        $organizationManager = Role::findByName('organization-manager', 'web');

        $this->assertTrue($saleManager->hasPermissionTo('sale.item_edit'));
        $this->assertTrue($saleManager->hasPermissionTo('sale.order_view'));
        $this->assertTrue($administrator->hasPermissionTo('administrator.organization_view'));
        $this->assertTrue($organizationManager->hasPermissionTo('organization.order_approve'));
        $this->assertFalse($saleManager->hasPermissionTo('administrator.organization_view'));
    }

    #[Test]
    public function sync_creates_super_admin_with_all_permissions(): void
    {
        $this->artisan('permissions:sync')
            ->assertSuccessful();

        $superAdmin = Role::findByName('super-admin', 'web');

        $this->assertTrue($superAdmin->hasPermissionTo('sale.item_edit'));
        $this->assertTrue($superAdmin->hasPermissionTo('administrator.organization_delete'));
        $this->assertTrue($superAdmin->hasPermissionTo('organization.order_reject'));
    }

    #[Test]
    public function sync_full_syncs_predefined_role_when_permissions_change(): void
    {
        Permission::findOrCreate('sale.item_view', 'web');

        $role = Role::findOrCreate('sale-manager', 'web');
        $role->syncPermissions(['sale.item_view']);

        $this->artisan('permissions:sync')
            ->assertSuccessful();

        $role->refresh();

        $this->assertTrue($role->hasPermissionTo('sale.item_edit'));
        $this->assertTrue($role->hasPermissionTo('sale.order_view'));
    }

    #[Test]
    public function sync_assigns_new_module_permissions_to_custom_roles_with_existing_module_access(): void
    {
        Permission::findOrCreate('sale.item_view', 'web');

        $role = Role::findOrCreate('content-editor', 'web');
        $role->givePermissionTo('sale.item_view');

        $user = User::factory()->create();
        $user->assignRole($role);

        $this->artisan('permissions:sync')
            ->assertSuccessful();

        $role->refresh();

        $this->assertTrue($role->hasPermissionTo('sale.item_edit'));
        $this->assertTrue($user->can('sale.item_edit'));
    }

    #[Test]
    public function sync_skips_role_assignment_when_no_roles_flag_is_set(): void
    {
        Permission::findOrCreate('sale.item_view', 'web');

        $role = Role::findOrCreate('content-editor', 'web');
        $role->givePermissionTo('sale.item_view');

        $this->artisan('permissions:sync --no-roles')
            ->assertSuccessful();

        $role->refresh();

        $this->assertFalse($role->hasPermissionTo('sale.item_edit'));
        $this->assertDatabaseMissing('roles', [
            'name' => 'super-admin',
            'guard_name' => 'web',
        ]);
    }
}
