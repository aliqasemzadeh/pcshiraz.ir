<?php

namespace App\Enums;

enum PriceTypeEnum: string
{
    case Cash = 'cash';
    case Wholesale = 'wholesale';
    case Installment = 'installment';
    case Corporate = 'corporate';
    case Credit = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('enums.price_type.cash'),
            self::Wholesale => __('enums.price_type.wholesale'),
            self::Installment => __('enums.price_type.installment'),
            self::Corporate => __('enums.price_type.corporate'),
            self::Credit => __('enums.price_type.credit'),
        };
    }

    public static function labels(): array
    {
        return [
            self::Cash->value => self::Cash->label(),
            self::Wholesale->value => self::Wholesale->label(),
            self::Installment->value => self::Installment->label(),
            self::Corporate->value => self::Corporate->label(),
            self::Credit->value => self::Credit->label(),
        ];
    }
}
