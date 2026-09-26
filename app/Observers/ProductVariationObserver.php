<?php

namespace App\Observers;

use App\Models\ProductVariation;
use App\Traits\OptimizesImages;

class ProductVariationObserver
{
    use OptimizesImages;

    public function saving(ProductVariation $variation): void
    {
        if ($variation->isDirty('image')) {
            $variation->image = $this->optimizeImage($variation->image);
        }
    }
}