<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('general', function ($group): void {
            $group->add('site_name', config('app.name', 'PC Shiraz'));
            $group->add('site_short_name', 'PCS');
            $group->add('site_description', '');
            $group->add('site_tags', []);
            $group->add('locale', 'fa');
            $group->add('timezone', 'Asia/Tehran');
            $group->add('logo_path', null);
            $group->add('favicon_path', null);
        });
    }
};
