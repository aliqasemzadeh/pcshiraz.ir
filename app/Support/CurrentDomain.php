<?php

namespace App\Support;

use App\Models\Domain;
use Illuminate\Support\Facades\Request;

class CurrentDomain
{
    protected static ?Domain $resolved = null;

    protected static bool $attempted = false;

    public static function get(): ?Domain
    {
        if (static::$attempted) {
            return static::$resolved;
        }

        static::$attempted = true;

        $host = Request::getHost();

        static::$resolved = Domain::query()
            ->where('domain', $host)
            ->first()
            ?? Domain::query()->orderBy('id')->first();

        return static::$resolved;
    }

    public static function flush(): void
    {
        static::$resolved = null;
        static::$attempted = false;
    }
}
