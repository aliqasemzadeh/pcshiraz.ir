<?php

use Illuminate\Support\Str;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->inGroup('maintenance', function ($group): void {
            $group->add('enabled', false);
            $group->add('secret', Str::lower(Str::random(16)));
            $group->add('message', 'سایت موقتاً در دسترس نیست. لطفاً بعداً مراجعه کنید.');
        });
    }
};
