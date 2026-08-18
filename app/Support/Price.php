<?php

namespace App\Support;

use App\Enums\PriceUnitEnum;
use App\Settings\GeneralSettings;

class Price
{
    public static function unit(): PriceUnitEnum
    {
        try {
            $value = app(GeneralSettings::class)->price_unit;

            return PriceUnitEnum::tryFrom($value) ?? PriceUnitEnum::Toman;
        } catch (\Throwable) {
            return PriceUnitEnum::Toman;
        }
    }

    public static function toDisplay(int|string|float|null $toman): float
    {
        $amount = (float) ($toman ?? 0);

        return self::unit() === PriceUnitEnum::Rial
            ? round($amount * 10, 4)
            : $amount;
    }

    public static function unmask(int|string|float|null $value): string
    {
        if ($value === null) {
            return '';
        }

        return str_replace([',', ' '], '', (string) $value);
    }

    public static function fromDisplay(int|string|float|null $display): float
    {
        $amount = (float) self::unmask($display);

        return self::unit() === PriceUnitEnum::Rial
            ? round($amount / 10, 4)
            : $amount;
    }

    public static function formatNumber(int|string|float|null $toman): string
    {
        return number_format(self::toDisplay($toman));
    }

    public static function format(int|string|float|null $toman): string
    {
        return self::formatNumber($toman).' '.self::unitLabel();
    }

    public static function unitLabel(): string
    {
        return self::unit()->label();
    }

    public static function toWords(int|string|float|null $toman): string
    {
        return PersianNumberToWords::convert(self::toDisplay($toman)).' '.self::unitLabel();
    }
}
