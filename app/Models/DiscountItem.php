<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DiscountItem extends Model
{
    protected $fillable = ['discount_id', 'applicable_type', 'applicable_id'];

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function applicable(): MorphTo
    {
        return $this->morphTo();
    }
}
