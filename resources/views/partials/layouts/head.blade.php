@php
    use App\Settings\GeneralSettings;
    use Illuminate\Support\Facades\Storage;

    $siteSettings = null;

    try {
        $siteSettings = app(GeneralSettings::class);
    } catch (\Throwable) {
        $siteSettings = null;
    }

    $siteName = $siteSettings?->site_name ?: config('app.name');
    $siteDescription = $siteSettings?->site_description ?: '';
    $siteTags = $siteSettings?->site_tags ?? [];
    $faviconPath = $siteSettings?->favicon_path;
    $faviconUrl = $faviconPath ? Storage::disk('public')->url($faviconPath) : null;
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if ($siteDescription !== '')
        <meta name="description" content="{{ $siteDescription }}">
    @endif
    @if ($siteTags !== [])
        <meta name="keywords" content="{{ implode(', ', $siteTags) }}">
    @endif

    <title>{{ $title ?? $siteName }}</title>

    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}" type="image/png">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
