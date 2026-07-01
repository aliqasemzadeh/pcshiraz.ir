<?php

namespace App\Enums;

enum ItemTypeEnum: string
{
    case Product = 'product';
    case Service = 'service';
    case Digital = 'digital';

    public function label(): string
    {
        return match ($this) {
            self::Product => __('enums.item_type.product'),
            self::Service => __('enums.item_type.service'),
            self::Digital => __('enums.item_type.digital'),
        };
    }

    public static function labels(): array
    {
        return [
            self::Product->value => self::Product->label(),
            self::Service->value => self::Service->label(),
            self::Digital->value => self::Digital->label(),
        ];
    }
}
