<?php

namespace App\Observers;

use App\Models\Product;
use App\Traits\OptimizesImages;

class ProductObserver
{
    use OptimizesImages;

    public function saving(Product $product): void
    {
        if ($product->isDirty('image')) {
            $product->image = $this->optimizeImage($product->image);
        }

        if ($product->isDirty('images') && is_array($product->images)) {
            $optimizedImages = [];
            foreach ($product->images as $img) {
                $optimizedImages[] = $this->optimizeImage($img);
            }
            $product->images = $optimizedImages;
        }
    }
}