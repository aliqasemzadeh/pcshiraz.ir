<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'title',
    'holder_name',
    'card_number',
    'bank_name',
    'is_default',
])]
class UserPaymentCard extends Model
{
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function maskedCardNumber(): Attribute
    {
        return Attribute::get(function (): string {
            $digits = preg_replace('/\D+/', '', (string) $this->card_number) ?: '';

            if (strlen($digits) < 4) {
                return $digits;
            }

            return '****-****-****-'.substr($digits, -4);
        });
    }
}
