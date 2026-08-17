<?php

namespace App\Console\Commands;

use App\Services\Administrator\PermissionSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('permissions:sync {--no-roles : Skip syncing predefined roles and assigning new permissions to custom roles}')]
#[Description('Sync permissions and roles from lang files to database')]
class UpdatePermissionCommand extends Command
{
    public function handle(PermissionSyncService $syncService): int
    {
        $result = $syncService->sync(assignToRoles: ! $this->option('no-roles'));

        $this->info("Synced {$result['total']} permission(s).");
        $this->line("  Created: {$result['created']}");
        $this->line("  Existing: {$result['existing']}");

        if (! $this->option('no-roles')) {
            $this->newLine();
            $this->info("Synced {$result['roles_total']} role(s).");
            $this->line("  Created: {$result['roles_created']}");
            $this->line("  Existing: {$result['roles_existing']}");
            $this->line("  Permissions synced: {$result['permissions_synced']}");
            $this->line("  Assigned to custom roles: {$result['assigned_to_roles']}");
        }

        return self::SUCCESS;
    }
}
