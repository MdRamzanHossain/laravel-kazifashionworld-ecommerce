<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'has_variations' => 'boolean',
        'attributes_schema' => 'array',
    ];

    public function getThumbAttribute()
    {
        if (!$this->image) return null;
        
        $thumbPath = preg_replace('/\.[a-zA-Z]+$/', '-thumb.webp', $this->image);
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($thumbPath)) {
            return $thumbPath;
        }
        
        return $this->image;
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function flashSaleProducts(): HasMany
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function flashSales(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(FlashSale::class, 'flash_sale_products')
            ->withPivot(['id', 'flash_price', 'quantity_limit', 'sold_count', 'is_active'])
            ->withTimestamps();
    }

    /**
     * Get active flash sale deal for this product (if any).
     */
    public function getActiveFlashSaleDeal(): ?FlashSaleProduct
    {
        $now = \Carbon\Carbon::now();
        return FlashSaleProduct::where('product_id', $this->id)
            ->where('is_active', true)
            ->whereHas('flashSale', function ($q) use ($now) {
                $q->where('is_active', true)
                  ->where('start_time', '<=', $now)
                  ->where('end_time', '>=', $now);
            })
            ->first();
    }

    /**
     * Get current effective selling price considering active Flash Sales and standard Sales.
     */
    public function getCurrentPriceAttribute(): float
    {
        $flashDeal = $this->getActiveFlashSaleDeal();
        if ($flashDeal && $flashDeal->flash_price > 0) {
            return (float) $flashDeal->flash_price;
        }

        if ($this->sale_price && $this->sale_price > 0 && $this->sale_price < $this->price) {
            return (float) $this->sale_price;
        }

        return (float) $this->price;
    }

    /**
     * Check if product is on active flash sale.
     */
    public function getIsOnFlashSaleAttribute(): bool
    {
        return $this->getActiveFlashSaleDeal() !== null;
    }

    /**
     * Get all resolved gallery image URLs for this product.
     */
    public function getGalleryImageUrlsAttribute(): array
    {
        $resolveImageUrl = function($path) {
            if (empty($path)) return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            if (str_starts_with($path, 'images/')) {
                return asset($path);
            }
            return asset('storage/' . $path);
        };

        $rawImages = array_filter(array_merge(
            [$this->image],
            is_array($this->images) ? $this->images : [],
            $this->relationLoaded('productImages') && $this->productImages ? $this->productImages->pluck('image_path')->toArray() : []
        ));

        $galleryImages = array_values(array_filter(array_unique(array_map($resolveImageUrl, $rawImages))));
        if (empty($galleryImages)) {
            $galleryImages = [asset('images/logo.png')];
        }

        return $galleryImages;
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->where('is_approved', true)->avg('rating') ?: 0;
    }

    public function getApprovedReviewsCountAttribute()
    {
        return $this->reviews()->where('is_approved', true)->count();
    }

    public function crossSells()
    {
        return $this->belongsToMany(Product::class, 'product_cross_sells', 'product_id', 'cross_sell_id');
    }
}
