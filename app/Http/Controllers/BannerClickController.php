<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class BannerClickController extends Controller
{
    public function __invoke(Banner $banner): RedirectResponse
    {
        abort_unless($banner->is_active, 404);

        Banner::query()->whereKey($banner->id)->increment('clicks_count');

        $url = trim($banner->link_url);

        if ($url === '') {
            return redirect()->route('home');
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return redirect()->away($url);
        }

        if (Str::startsWith($url, '/')) {
            return redirect()->to($url);
        }

        return redirect()->away('https://'.$url);
    }
}
