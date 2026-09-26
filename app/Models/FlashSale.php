<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FlashSale extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
        'is_active'  => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title) . '-' . Str::random(5);
            }
        });
    }

    public function flashSaleProducts(): HasMany
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'flash_sale_products')
            ->withPivot(['id', 'flash_price', 'quantity_limit', 'sold_count', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Check if flash sale campaign is currently active and within time boundaries.
     */
    public function isActiveNow(): bool
    {
        $now = Carbon::now();
        return $this->is_active && $this->start_time <= $now && $this->end_time >= $now;
    }

    /**
     * Get remaining time in seconds for live countdown.
     */
    public function getRemainingSecondsAttribute(): int
    {
        $now = Carbon::now();
        if ($this->end_time && $this->end_time->isFuture()) {
            return max(0, $this->end_time->timestamp - $now->timestamp);
        }
        return 0;
    }

    /**
     * Campaign status badge text.
     */
    public function getStatusLabelAttribute(): string
    {
        $now = Carbon::now();
        if (!$this->is_active) {
            return 'Draft / Inactive';
        }
        if ($this->start_time > $now) {
            return 'Scheduled';
        }
        if ($this->end_time < $now) {
            return 'Expired';
        }
        return 'Live Now';
    }
}
