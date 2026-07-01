<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['domain_id', 'title', 'description'])]
class Inventory extends Model
{
    use SoftDeletes;

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
