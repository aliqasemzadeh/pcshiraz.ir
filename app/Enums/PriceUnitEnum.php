<?php

namespace App\Enums;

enum PriceUnitEnum: string
{
    case Rial = 'rial';
    case Toman = 'toman';

    public function label(): string
    {
        return match ($this) {
            self::Rial => __('enums.price_unit.rial'),
            self::Toman => __('enums.price_unit.toman'),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Rial->value => self::Rial->label(),
            self::Toman->value => self::Toman->label(),
        ];
    }
}
