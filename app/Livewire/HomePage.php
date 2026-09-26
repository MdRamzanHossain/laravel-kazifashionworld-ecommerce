<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Url;
use Livewire\Component;

class HomePage extends Component
{
    #[Url(as: 'category', history: true)]
    public $selectedCategory = 'all';

    #[Url(as: 'search', history: true)]
    public $search = '';

    public $addedProductId = null;

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
    }

    public function clearSearch()
    {
        $this->search = '';
    }

    public function addToCart($productId, $quantity = 1, $variationId = null, $attributesString = null)
    {
        $product = Product::findOrFail($productId);
        if ($product->has_variations && !$variationId && !$attributesString) {
            session()->flash('cart_error', 'Please select an option before adding to bag.');
            $this->redirectRoute('product.detail', ['slug' => $product->slug]);
            return;
        }
        
        $qty = max(1, (int)$quantity);
        CartService::add($productId, $qty, $variationId, $attributesString);
        $this->dispatch('cart-updated');
        $this->addedProductId = $productId;

        // Flash message
        session()->flash('cart_success', "{$product->name} added to bag!");
    }

    public function render()
    {
                $categories = \Illuminate\Support\Facades\Cache::remember('home_categories', 3600, function () {
            return Category::with(['children' => fn ($q) => $q->where('is_active', true)])
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->withCount('products')
                ->get();
        });

                $productsQuery = Product::with(['category.parent', 'brand'])
            ->where('is_active', true);

        if (!empty($this->search)) {
            $searchTerm = trim($this->search);
            $productsQuery->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('short_description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('sku', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($this->selectedCategory !== 'all') {
            if ($this->selectedCategory === 'featured') {
                $productsQuery->where('is_featured', true);
            } elseif ($this->selectedCategory === 'sale') {
                $productsQuery->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
            } else {
                $categoryId = (int) $this->selectedCategory;
                // Include products belonging to this parent category OR any of its subcategories
                $categoryIds = Category::where('id', $categoryId)
                    ->orWhere('parent_id', $categoryId)
                    ->pluck('id');

                $productsQuery->whereIn('category_id', $categoryIds);
            }
        }

        $products = $productsQuery->inRandomOrder()->take(12)->get();

                $flashDeals = Product::with(['category.parent'])
            ->where('is_active', true)
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->inRandomOrder()
            ->take(4)
            ->get();

                $heroProducts = Product::where('is_active', true)
            ->where('is_featured', true)
            ->inRandomOrder()
            ->take(3)
            ->get();

        if ($heroProducts->isEmpty()) {
            $heroProducts = Product::where('is_active', true)->inRandomOrder()->take(3)->get();
        }

                $activeFlashSale = \App\Models\FlashSale::with([
            'flashSaleProducts' => function ($q) {
                $q->where('is_active', true)->with('product.category.parent');
            }
        ])
        ->where('is_active', true)
        ->where('start_time', '<=', \Carbon\Carbon::now())
        ->where('end_time', '>=', \Carbon\Carbon::now())
        ->latest()
        ->first();

                $videoReels = \App\Models\VideoReel::active()
            ->with(['product.category.parent'])
            ->get();

        return view('livewire.home-page', [
            'categories'      => $categories,
            'products'        => $products,
            'flashDeals'      => $flashDeals,
            'heroProducts'    => $heroProducts,
            'heroSettings'    => \App\Services\SettingService::getHeroSettings(),
            'activeFlashSale' => $activeFlashSale,
            'videoReels'      => $videoReels,
        ]);
    }
}