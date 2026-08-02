<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('social', function ($group): void {
            $group->add('telegram', '');
            $group->add('eitaa', '');
            $group->add('bale', '');
            $group->add('rubika', '');
            $group->add('soroush', '');
            $group->add('aparat', '');
            $group->add('instagram', '');
            $group->add('linkedin', '');
            $group->add('x', '');
        });
    }
};
