<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title',
    'description',
    'sort_order',
    'meta',
])]
class Inventory extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'sort_order' => 'integer',
        ];
    }
}
