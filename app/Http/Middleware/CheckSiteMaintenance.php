<?php

namespace App\Http\Middleware;

use App\Settings\MaintenanceSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSiteMaintenance
{
    public const COOKIE = 'maintenance_bypass';

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        try {
            $settings = app(MaintenanceSettings::class);
        } catch (\Throwable) {
            return $next($request);
        }

        if (! $settings->enabled) {
            return $next($request);
        }

        $secret = trim($settings->secret);

        if ($secret !== '' && $request->is($secret)) {
            $response = redirect()->route('home');

            return $response->withCookie(cookie(
                self::COOKIE,
                hash('sha256', $secret),
                60 * 24 * 30,
                '/',
                null,
                false,
                true,
                false,
                'lax'
            ));
        }

        if ($this->hasValidBypass($request, $secret)) {
            return $next($request);
        }

        return response()->view('errors.maintenance', [
            'message' => $settings->message,
        ], 503);
    }

    protected function shouldSkip(Request $request): bool
    {
        if ($request->is('up') || $request->is('livewire/*') || $request->is('administrator') || $request->is('administrator/*')) {
            return true;
        }

        return false;
    }

    protected function hasValidBypass(Request $request, string $secret): bool
    {
        if ($secret === '') {
            return false;
        }

        $cookie = (string) $request->cookie(self::COOKIE, '');

        return hash_equals(hash('sha256', $secret), $cookie);
    }
}
