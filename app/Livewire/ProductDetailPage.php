<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use App\Services\CartService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class ProductDetailPage extends Component
{
    use WithFileUploads;

    public $slug;
    public $quantity = 1;
    public $activeImage = null;
    public $activeTab = 'description';
    public $selectedVariationId = null;
    public $selectedAttributes = [];
    public $allAttributesSelected = false;

    public function selectAttribute($attributeName, $value)
    {
        $this->selectedAttributes[$attributeName] = $value;
        $this->resolveVariation();
    }

    protected function resolveVariation()
    {
        $product = \App\Models\Product::where('slug', $this->slug)->first();
        if (!$product || empty($product->attributes_schema)) return;

        $expectedNameParts = [];
        $this->allAttributesSelected = true;

        foreach ($product->attributes_schema as $attr) {
            if (empty($this->selectedAttributes[$attr['name']])) {
                $this->allAttributesSelected = false;
                break;
            }
            $expectedNameParts[] = $this->selectedAttributes[$attr['name']];
        }

        if ($this->allAttributesSelected) {
            $expectedName = implode(' - ', $expectedNameParts);
            
            // 1. Exact match (e.g. Red - XL)
            $variation = $product->variations()->where('variation_name', $expectedName)->first();
            
            // 2. Partial match for images/price (e.g. Red)
            if (!$variation) {
                $variation = $product->variations()->whereIn('variation_name', $expectedNameParts)->first();
            }
            
            if ($variation) {
                // If it's a partial match, we might not want to set selectedVariationId if we only want the image
                // But let's set it so the cart can pick up the overridden price/image, but we also pass the full attributes string to cart!
                $this->selectedVariationId = $variation->id;
                if ($variation->image) {
                    $this->activeImage = $variation->image;
                }
            } else {
                // It's perfectly fine if there is no variation in the DB! 
                // We will just pass the attributes string to the cart.
                $this->selectedVariationId = 'NO_DB_VAR';
                $this->allAttributesSelected = true; 
            }
        } else {
            $this->selectedVariationId = null;
        }
    }

    // Review Form State
    public $reviewRating = 5;
    public $reviewTitle = '';
    public $reviewComment = '';
    public $reviewImages = [];

    // Cross Sells State
    public $selectedCrossSells = [];

    public function mount($slug)
    {
        $this->slug = $slug;
        $product = Product::with('crossSells')->where('slug', $this->slug)->firstOrFail();
        $this->activeImage = $product->image;

        if ($product->crossSells) {
            $this->selectedCrossSells = $product->crossSells->where('is_active', true)->pluck('id')->toArray();
        }
    }

    public function submitReview()
    {
        if (!Auth::check()) {
            session()->flash('review_error', 'You must be logged in to submit a review.');
            return;
        }

        $this->validate([
            'reviewRating' => 'required|integer|min:1|max:5',
            'reviewTitle' => 'nullable|string|max:255',
            'reviewComment' => 'nullable|string',
            'reviewImages.*' => 'image|max:2048', // Max 2MB per image
        ]);

        $product = Product::where('slug', $this->slug)->firstOrFail();
        
        $imagePaths = [];
        foreach ($this->reviewImages as $image) {
            $imagePaths[] = $image->store('reviews', 'public');
        }

        // Check if user actually ordered this product
        $isVerified = Order::where('user_id', Auth::id())
            ->whereHas('orderItems', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })->exists();

        Review::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'rating' => $this->reviewRating,
            'title' => $this->reviewTitle,
            'comment' => $this->reviewComment,
            'images' => $imagePaths,
            'is_verified_purchase' => $isVerified,
            'is_approved' => false,
        ]);

        $this->reset(['reviewRating', 'reviewTitle', 'reviewComment', 'reviewImages']);
        session()->flash('review_success', 'Your review has been submitted and is awaiting approval.');
    }

    public function setActiveImage($image)
    {
        $this->activeImage = $image;
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    public function selectVariation($variationId)
    {
        $this->selectedVariationId = $variationId;
        $variation = \App\Models\ProductVariation::find($variationId);
        if ($variation && $variation->image) {
            $this->activeImage = $variation->image;
        }
        $this->quantity = 1; // Reset quantity on variation change
    }

    public function increaseQuantity()
    {
        $product = Product::where('slug', $this->slug)->first();
        $maxStock = $product->stock_quantity ?? 99;
        
        if ($this->selectedVariationId) {
            $variation = \App\Models\ProductVariation::find($this->selectedVariationId);
            $maxStock = $variation ? $variation->stock_quantity : $maxStock;
        }
        
        if ($this->quantity < $maxStock) {
            $this->quantity++;
        }
    }

    public function decreaseQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart()
    {
        $product = Product::with('variations')->where('slug', $this->slug)->firstOrFail();
        
        if ($product->has_variations && !$this->selectedVariationId) {
            session()->flash('cart_error', 'Please select an option before adding to bag.');
            return;
        }
        
        $attributesString = '';
        if (!empty($this->selectedAttributes)) {
            $parts = [];
            foreach ($this->selectedAttributes as $k => $v) {
                $parts[] = $k . ': ' . $v;
            }
            $attributesString = implode(', ', $parts);
        }
        $dbVariationId = ($this->selectedVariationId === 'NO_DB_VAR') ? null : $this->selectedVariationId;
        CartService::add($product->id, $this->quantity, $dbVariationId, $attributesString);
        $this->dispatch('cart-updated');
        
        $this->dispatch('track-add-to-cart', [
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'price'        => $product->sale_price ?? $product->price
        ]);
        
        $variationName = '';
        if (!empty($attributesString)) {
            $variationName = ' - ' . $attributesString;
        } else if ($dbVariationId) {
            $var = \App\Models\ProductVariation::find($dbVariationId);
            $variationName = $var ? ' - ' . $var->variation_name : '';
        }
        
        session()->flash('cart_success', "{$product->name}{$variationName} (x{$this->quantity}) added to your bag!");
    }

    public function addFrequentlyBoughtTogetherToCart()
    {
        $product = Product::with('variations')->where('slug', $this->slug)->firstOrFail();
        
        if ($product->has_variations && !$this->selectedVariationId) {
            session()->flash('cart_error', 'Please select an option before adding to bag.');
            return;
        }
        
        $attributesString = '';
        if (!empty($this->selectedAttributes)) {
            $parts = [];
            foreach ($this->selectedAttributes as $k => $v) {
                $parts[] = $k . ': ' . $v;
            }
            $attributesString = implode(', ', $parts);
        }
        $dbVariationId = ($this->selectedVariationId === 'NO_DB_VAR') ? null : $this->selectedVariationId;
        CartService::add($product->id, $this->quantity, $dbVariationId, $attributesString);
        
        foreach ($this->selectedCrossSells as $crossSellId) {
            $crossSellProduct = Product::find($crossSellId);
            if ($crossSellProduct && !$crossSellProduct->has_variations) {
                CartService::add($crossSellProduct->id, 1);
            }
        }
        
        $this->dispatch('cart-updated');
        session()->flash('cart_success', 'Frequently Bought Together items added to your bag!');
    }

    public function buyNow()
    {
        $product = Product::with('variations')->where('slug', $this->slug)->firstOrFail();
        
        if ($product->has_variations && !$this->selectedVariationId) {
            session()->flash('cart_error', 'Please select an option before proceeding to checkout.');
            return;
        }
        
        $attributesString = '';
        if (!empty($this->selectedAttributes)) {
            $parts = [];
            foreach ($this->selectedAttributes as $k => $v) {
                $parts[] = $k . ': ' . $v;
            }
            $attributesString = implode(', ', $parts);
        }
        $dbVariationId = ($this->selectedVariationId === 'NO_DB_VAR') ? null : $this->selectedVariationId;
        CartService::add($product->id, $this->quantity, $dbVariationId, $attributesString);
        $this->dispatch('cart-updated');
        return redirect()->route('checkout');
    }

    public function render()
    {
        $product = Product::with(['category.parent', 'brand'])
            ->where('slug', $this->slug)
            ->firstOrFail();

        // If active image wasn't set or changed
        if (!$this->activeImage && $product->image) {
            $this->activeImage = $product->image;
        }

        // Related / Recommended products from same category
        $relatedProducts = Product::with(['category.parent'])
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                if ($product->category_id) {
                    $q->where('category_id', $product->category_id);
                }
            })
            ->latest()
            ->take(4)
            ->get();

        // Fallback to general products if related is empty
        if ($relatedProducts->isEmpty()) {
            $relatedProducts = Product::where('is_active', true)
                ->where('id', '!=', $product->id)
                ->latest()
                ->take(4)
                ->get();
        }

        $reviews = $product->reviews()->with('user')->where('is_approved', true)->latest()->get();

        return view('livewire.product-detail-page', [
            'product'         => $product,
            'relatedProducts' => $relatedProducts,
            'reviews'         => $reviews,
            'quantity'        => $this->quantity,
            'activeTab'       => $this->activeTab,
        ]);
    }
}