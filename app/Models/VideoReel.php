<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VideoReel extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
        'views_count' => 'integer',
        'custom_price' => 'decimal:2',
        'custom_original_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get display category name
     */
    public function getDisplayCategoryAttribute(): string
    {
        if (!empty($this->custom_category_name)) {
            return $this->custom_category_name;
        }

        return $this->product?->category?->name ?? 'Beauty';
    }

    /**
     * Get display product title
     */
    public function getDisplayProductNameAttribute(): string
    {
        if (!empty($this->custom_product_name)) {
            return $this->custom_product_name;
        }

        return $this->product?->name ?? $this->title;
    }

    /**
     * Get display price
     */
    public function getDisplayPriceAttribute(): float
    {
        if ($this->custom_price !== null && $this->custom_price > 0) {
            return (float) $this->custom_price;
        }

        return (float) ($this->product?->price ?? 0);
    }

    /**
     * Get display original/strikethrough price
     */
    public function getDisplayOriginalPriceAttribute(): ?float
    {
        if ($this->custom_original_price !== null && $this->custom_original_price > 0) {
            return (float) $this->custom_original_price;
        }

        return $this->product?->original_price ? (float) $this->product->original_price : null;
    }

    /**
     * Get product thumbnail image URL
     */
    public function getDisplayProductImageAttribute(): string
    {
        if (!empty($this->custom_product_image)) {
            return asset('storage/' . $this->custom_product_image);
        }

        if ($this->product) {
            if (!empty($this->product->image)) {
                return asset('storage/' . $this->product->image);
            }
            if (!empty($this->product->images) && is_array($this->product->images) && count($this->product->images) > 0) {
                return asset('storage/' . $this->product->images[0]);
            }
        }

        return asset('images/logo.png');
    }

    /**
     * Get video stream URL
     */
    public function getDisplayVideoSrcAttribute(): ?string
    {
        if (!empty($this->video_file)) {
            return asset('storage/' . $this->video_file);
        }

        if (!empty($this->video_url)) {
            return $this->video_url;
        }

        return null;
    }

    /**
     * Get poster cover image URL
     */
    public function getDisplayPosterSrcAttribute(): string
    {
        if (!empty($this->poster_image)) {
            return asset('storage/' . $this->poster_image);
        }

        return $this->getDisplayProductImageAttribute();
    }

    /**
     * Scope for active reels ordered by sort_order
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('id', 'desc');
    }
}