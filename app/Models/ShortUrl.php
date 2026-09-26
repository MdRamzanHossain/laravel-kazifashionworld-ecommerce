<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShortUrl extends Model
{
    protected $guarded = [];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function clicksHistory(): HasMany
    {
        return $this->hasMany(ShortUrlClick::class);
    }

    public function getShortUrlAttribute(): string
    {
        return url('/s/' . $this->code);
    }
}
