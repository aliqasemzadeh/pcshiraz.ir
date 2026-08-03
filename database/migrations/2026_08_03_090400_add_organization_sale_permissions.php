<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'administrator.organization_view',
            'administrator.organization_create',
            'administrator.organization_edit',
            'administrator.organization_delete',
            'sale.order_view',
            'sale.order_edit',
            'sale.installment_plan_view',
            'sale.installment_plan_create',
            'sale.installment_plan_edit',
            'sale.installment_plan_delete',
            'organization.order_view',
            'organization.order_approve',
            'organization.order_reject',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }
    }

    public function down(): void
    {
        $permissions = [
            'administrator.organization_view',
            'administrator.organization_create',
            'administrator.organization_edit',
            'administrator.organization_delete',
            'sale.order_view',
            'sale.order_edit',
            'sale.installment_plan_view',
            'sale.installment_plan_create',
            'sale.installment_plan_edit',
            'sale.installment_plan_delete',
            'organization.order_view',
            'organization.order_approve',
            'organization.order_reject',
        ];

        Permission::query()->whereIn('name', $permissions)->delete();
    }
};
