<?php

namespace App\Console\Commands;

use App\Services\Administrator\PermissionSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('permissions:sync {--no-roles : Skip auto-assigning new permissions to roles}')]
#[Description('Sync permissions from lang files to database')]
class UpdatePermissionCommand extends Command
{
    public function handle(PermissionSyncService $syncService): int
    {
        $result = $syncService->sync(assignToRoles: ! $this->option('no-roles'));

        $this->info("Synced {$result['total']} permission(s).");
        $this->line("  Created: {$result['created']}");
        $this->line("  Existing: {$result['existing']}");

        if (! $this->option('no-roles')) {
            $this->line("  Assigned to roles: {$result['assigned_to_roles']}");
        }

        return self::SUCCESS;
    }
}
