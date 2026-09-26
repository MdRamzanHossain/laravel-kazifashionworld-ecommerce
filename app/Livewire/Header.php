<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class Header extends Component
{
    public string $search = '';
    public bool $isOpen = false;
    public int $cartCount = 0;

    /**
     * Popular and trending searches to suggest
     */
    public array $trendingSearches = [
        'Serum',
        'Face Wash',
        'Sunscreen',
        'Moisturizer',
        'Sharara',
        'Gown',
        'Lipstick',
        'Toner',
    ];

    public function mount()
    {
        $this->cartCount = CartService::getCount();
    }

    #[On('cart-updated')]
    public function updateCartCount()
    {
        $this->cartCount = CartService::getCount();
    }

    public function updatedSearch()
    {
        $this->isOpen = true;
    }

    public function openSearch()
    {
        $this->isOpen = true;
    }

    public function closeSearch()
    {
        $this->isOpen = false;
    }

    public function performSearch()
    {
        $term = trim($this->search);
        if (!empty($term)) {
            $this->isOpen = false;
            return redirect()->route('home', ['search' => $term]);
        }
    }

    public function selectTrending(string $keyword)
    {
        $this->search = $keyword;
        $this->isOpen = false;
        return redirect()->route('home', ['search' => $keyword]);
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->isOpen = false;
    }

    public function render()
    {
        $searchResults = collect();
        $matchedCategories = collect();
        $totalResultsCount = 0;

        $searchTerm = trim($this->search);

        if (strlen($searchTerm) >= 2) {
            // 1. Search products
            $productQuery = Product::with(['category.parent', 'brand'])
                ->where('is_active', true)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('short_description', 'like', "%{$searchTerm}%")
                      ->orWhere('sku', 'like', "%{$searchTerm}%");
                });

            $totalResultsCount = (clone $productQuery)->count();
            $searchResults = $productQuery->latest()->take(6)->get();

            // 2. Search categories
            $matchedCategories = Category::with('parent')
                ->where('is_active', true)
                ->where('name', 'like', "%{$searchTerm}%")
                ->take(4)
                ->get();
        }

        // 3. Navigation Categories for Mega-Menu and Mobile Drawer
        $navCategories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('name')
            ->take(8)
            ->get();

        $hasActiveFlashSale = \App\Models\FlashSale::where('is_active', true)
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->exists();

        return view('livewire.header', [
            'searchResults'      => $searchResults,
            'matchedCategories'  => $matchedCategories,
            'totalResultsCount'  => $totalResultsCount,
            'navCategories'      => $navCategories,
            'hasActiveFlashSale' => $hasActiveFlashSale,
        ]);
    }
}
