<?php

use App\Support\Price;

if (! function_exists('format_price')) {
    function format_price(int|string|float|null $toman): string
    {
        return Price::format($toman);
    }
}

if (! function_exists('format_price_number')) {
    function format_price_number(int|string|float|null $toman): string
    {
        return Price::formatNumber($toman);
    }
}

if (! function_exists('price_unit_label')) {
    function price_unit_label(): string
    {
        return Price::unitLabel();
    }
}

if (! function_exists('price_in_words')) {
    function price_in_words(int|string|float|null $toman): string
    {
        return Price::toWords($toman);
    }
}
