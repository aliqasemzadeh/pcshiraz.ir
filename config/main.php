<?php

use App\Enums\PriceTypeEnum;

return [
    'per_page' => 30,
    'default_price_type' => PriceTypeEnum::Cash,
    'demo_media_proxy' => env('DEMO_MEDIA_PROXY'),
];
