<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariation;

class CartService
{
    public static function getCart(): array
    {
        $cart = session()->get('cart', []);
        $updated = false;

        foreach ($cart as $cartKey => &$item) {
            $product = Product::find($item['id']);
            if ($product) {
                // Determine prices based on variation or base product
                $effectivePrice = $product->current_price;
                $originalPrice = (float) $product->price;
                $isFlash = $product->is_on_flash_sale;
                
                if (isset($item['variation_id'])) {
                    $variation = ProductVariation::find($item['variation_id']);
                    if ($variation) {
                        $effectivePrice = $variation->sale_price ?: ($variation->price ?: $effectivePrice);
                        $originalPrice = $variation->price ?: $originalPrice;
                    }
                }

                if (!isset($item['cart_key'])) {
                    $item['cart_key'] = $cartKey;
                    $updated = true;
                }

                if (!isset($item['original_price']) || $item['price'] != $effectivePrice || ($item['is_flash_sale'] ?? false) != $isFlash) {
                    $item['price'] = $effectivePrice;
                    $item['original_price'] = $originalPrice;
                    $item['is_flash_sale'] = $isFlash;
                    $updated = true;
                }
            }
        }

        if ($updated) {
            session()->put('cart', $cart);
        }

        return $cart;
    }

    public static function add($productId, $quantity = 1, $variationId = null, $attributesString = null): void
    {
        $cart = self::getCart();
        $product = Product::findOrFail($productId);
        
        $cartKey = $productId . ($variationId ? '_' . $variationId : '') . ($attributesString ? '_' . md5($attributesString) : '');
        
        $price = (float) $product->current_price;
        $originalPrice = (float) $product->price;
        $isFlashSale = (bool) $product->is_on_flash_sale;
        $image = $product->image;
        $variationName = null;

        if ($variationId) {
            $variation = ProductVariation::find($variationId);
            if ($variation) {
                $price = (float) ($variation->sale_price ?: ($variation->price ?: $price));
                $originalPrice = (float) ($variation->price ?: $originalPrice);
                $image = $variation->image ?: $image;
                $variationName = $attributesString ?: $variation->variation_name; // Prefer attributes string if passed
            }
        } elseif ($attributesString) {
            $variationName = $attributesString;
        }

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
            $cart[$cartKey]['price'] = $price;
            $cart[$cartKey]['original_price'] = $originalPrice;
            $cart[$cartKey]['is_flash_sale'] = $isFlashSale;
        } else {
            $cart[$cartKey] = [
                'cart_key'       => $cartKey,
                'id'             => $product->id,
                'variation_id'   => $variationId,
                'variation_name' => $variationName,
                'name'           => $product->name,
                'price'          => $price,
                'original_price' => $originalPrice,
                'is_flash_sale'  => $isFlashSale,
                'image'          => $image,
                'quantity'       => $quantity,
                'slug'           => $product->slug,
            ];
        }

        session()->put('cart', $cart);
    }

    public static function remove($cartKey): void
    {
        $cart = self::getCart();
        unset($cart[$cartKey]);
        session()->put('cart', $cart);
    }

    public static function updateQuantity($cartKey, $quantity): void
    {
        $cart = self::getCart();
        if (isset($cart[$cartKey]) && $quantity > 0) {
            $cart[$cartKey]['quantity'] = $quantity;
            session()->put('cart', $cart);
        }
    }

    public static function getTotal(): float
    {
        return array_reduce(self::getCart(), function ($sum, $item) {
            return $sum + ($item['price'] * $item['quantity']);
        }, 0);
    }

    public static function getCount(): int
    {
        return array_sum(array_column(self::getCart(), 'quantity'));
    }

    public static function clear(): void
    {
        session()->forget('cart');
    }
}
