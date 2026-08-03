<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $renames = [
        'administrator.banner_view' => 'sale.banner_view',
        'administrator.banner_create' => 'sale.banner_create',
        'administrator.banner_edit' => 'sale.banner_edit',
        'administrator.banner_delete' => 'sale.banner_delete',
        'administrator.article_view' => 'sale.article_view',
        'administrator.article_create' => 'sale.article_create',
        'administrator.article_edit' => 'sale.article_edit',
        'administrator.article_delete' => 'sale.article_delete',
    ];

    public function up(): void
    {
        foreach ($this->renames as $from => $to) {
            $this->renamePermission($from, $to);
        }
    }

    public function down(): void
    {
        foreach ($this->renames as $from => $to) {
            $this->renamePermission($to, $from);
        }
    }

    private function renamePermission(string $from, string $to): void
    {
        $fromPermission = DB::table('permissions')->where('name', $from)->first();

        if ($fromPermission === null) {
            return;
        }

        $toPermission = DB::table('permissions')->where('name', $to)->first();

        if ($toPermission === null) {
            DB::table('permissions')
                ->where('id', $fromPermission->id)
                ->update(['name' => $to]);

            return;
        }

        DB::table('role_has_permissions')
            ->where('permission_id', $fromPermission->id)
            ->whereNotIn('role_id', function ($query) use ($toPermission): void {
                $query->select('role_id')
                    ->from('role_has_permissions')
                    ->where('permission_id', $toPermission->id);
            })
            ->update(['permission_id' => $toPermission->id]);

        DB::table('model_has_permissions')
            ->where('permission_id', $fromPermission->id)
            ->whereNotIn('model_id', function ($query) use ($toPermission): void {
                $query->select('model_id')
                    ->from('model_has_permissions')
                    ->where('permission_id', $toPermission->id)
                    ->whereColumn('model_type', 'model_has_permissions.model_type');
            })
            ->update(['permission_id' => $toPermission->id]);

        DB::table('role_has_permissions')->where('permission_id', $fromPermission->id)->delete();
        DB::table('model_has_permissions')->where('permission_id', $fromPermission->id)->delete();
        DB::table('permissions')->where('id', $fromPermission->id)->delete();
    }
};
