<?php

namespace App\Services\Shop;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\HasMedia;
use Throwable;

class DemoMediaDownloader
{
    /**
     * Download a remote file (optionally via DEMO_MEDIA_PROXY) and attach it to a media collection.
     */
    public function attachFromUrl(HasMedia $model, string $url, string $collection, ?string $fileName = null): bool
    {
        if ($model->getFirstMedia($collection) !== null) {
            return false;
        }

        $contents = $this->fetch($url);

        if ($contents === null) {
            return false;
        }

        $fileName ??= basename(parse_url($url, PHP_URL_PATH) ?: 'demo-media.bin');

        try {
            $model
                ->addMediaFromString($contents)
                ->usingFileName($fileName)
                ->toMediaCollection($collection);
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    /**
     * Attach a local file to a media collection when missing.
     */
    public function attachFromPath(HasMedia $model, string $path, string $collection, ?string $fileName = null): bool
    {
        if ($model->getFirstMedia($collection) !== null || ! is_file($path)) {
            return false;
        }

        try {
            $model
                ->addMedia($path)
                ->preservingOriginal()
                ->usingFileName($fileName ?? basename($path))
                ->toMediaCollection($collection);
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    public function fetch(string $url): ?string
    {
        $options = [
            'timeout' => 30,
            'connect_timeout' => 10,
        ];

        $proxy = config('main.demo_media_proxy');

        if (is_string($proxy) && $proxy !== '') {
            $options['proxy'] = $proxy;
        }

        try {
            $response = Http::withOptions($options)
                ->withHeaders([
                    'User-Agent' => 'PCShiraz-DemoSeeder/1.0',
                    'Accept' => 'image/*,*/*',
                ])
                ->get($url);

            if (! $response->successful()) {
                return null;
            }

            $body = $response->body();

            return $body !== '' ? $body : null;
        } catch (ConnectionException|RequestException) {
            return null;
        }
    }
}
