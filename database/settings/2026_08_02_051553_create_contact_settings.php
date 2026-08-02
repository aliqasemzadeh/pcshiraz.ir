<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('contact', function ($group): void {
            $group->add('address', '');
            $group->add('postal_code', '');
            $group->add('fax', '');
            $group->add('support_email', '');
            $group->add('phones', []);
        });
    }
};
