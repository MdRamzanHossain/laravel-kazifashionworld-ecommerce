<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashSaleProduct extends Model
{
    protected $guarded = [];

    protected $casts = [
        'flash_price'    => 'decimal:2',
        'quantity_limit' => 'integer',
        'sold_count'     => 'integer',
        'is_active'      => 'boolean',
    ];

    public function flashSale(): BelongsTo
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get discount percentage relative to original product price.
     */
    public function getDiscountPercentageAttribute(): int
    {
        if ($this->product && $this->product->price > 0 && $this->flash_price < $this->product->price) {
            return (int) round((($this->product->price - $this->flash_price) / $this->product->price) * 100);
        }
        return 0;
    }

    /**
     * Get percentage of claimed / sold stock in flash sale.
     */
    public function getSoldPercentageAttribute(): int
    {
        if ($this->quantity_limit > 0) {
            return (int) min(100, round(($this->sold_count / $this->quantity_limit) * 100));
        }
        return 0;
    }
}
