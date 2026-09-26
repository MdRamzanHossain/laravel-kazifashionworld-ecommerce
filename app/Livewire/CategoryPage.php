<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryPage extends Component
{
    use WithPagination;

    public string $slug;

    #[Url(as: 'sub', history: true)]
    public $selectedSubcategory = 'all';

    #[Url(as: 'sort', history: true)]
    public $sortBy = 'latest';

    #[Url(as: 'in_stock', history: true)]
    public $inStockOnly = false;

    #[Url(as: 'q', history: true)]
    public $search = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function selectSubcategory($subId): void
    {
        $this->selectedSubcategory = $subId;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->selectedSubcategory = 'all';
        $this->sortBy = 'latest';
        $this->inStockOnly = false;
        $this->search = '';
        $this->resetPage();
    }

    public function addToCart($productId, $quantity = 1, $variationId = null)
    {
        $product = \App\Models\Product::findOrFail($productId);
        if ($product->has_variations && !$variationId) {
            session()->flash('cart_error', 'Please select an option before adding to bag.');
            $this->redirectRoute('product.detail', ['slug' => $product->slug]);
            return;
        }

        $qty = max(1, (int)$quantity);
        CartService::add($productId, $qty, $variationId);
        $this->dispatch('cart-updated');
        session()->flash('cart_success', "{$product->name} added to bag!");
    }

    public function render()
    {
        $category = Category::with(['children.products', 'parent.children.products'])
            ->where('slug', $this->slug)
            ->where('is_active', true)
            ->firstOrFail();

        // Determine Category Hierarchy & Subcategories for graphical selector
        $parentCategory = $category->isParent() ? $category : $category->parent;
        $subcategories = $parentCategory ? $parentCategory->children()->where('is_active', true)->withCount('products')->get() : collect();

        // Target Category IDs for Product Queries
        if ($category->isSubcategory()) {
            $targetCategoryIds = [$category->id];
        } else {
            if ($this->selectedSubcategory === 'all') {
                $targetCategoryIds = $category->getAllCategoryIds();
            } else {
                $targetCategoryIds = [(int) $this->selectedSubcategory];
            }
        }

        // Build Product Query
        $productsQuery = Product::with(['category.parent', 'brand', 'flashSaleProducts.flashSale'])
            ->where('is_active', true)
            ->whereIn('category_id', $targetCategoryIds);

        // Search Filter
        if (!empty($this->search)) {
            $term = trim($this->search);
            $productsQuery->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('short_description', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        // In-Stock Only Filter
        if ($this->inStockOnly) {
            $productsQuery->where('stock_quantity', '>', 0);
        }

        // Sorting
        switch ($this->sortBy) {
            case 'price_asc':
                $productsQuery->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $productsQuery->orderBy('price', 'desc');
                break;
            case 'sale':
                $productsQuery->where(function ($q) {
                    $q->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price');
                })->latest();
                break;
            default:
                $productsQuery->latest();
                break;
        }

        $products = $productsQuery->paginate(12);

        // Total products in this category hierarchy
        $totalCategoryProductsCount = Product::where('is_active', true)
            ->whereIn('category_id', $category->getAllCategoryIds())
            ->count();

        return view('livewire.category-page', [
            'category'                   => $category,
            'parentCategory'             => $parentCategory,
            'subcategories'              => $subcategories,
            'products'                   => $products,
            'totalCategoryProductsCount' => $totalCategoryProductsCount,
        ])->layout('components.layouts.app', [
            'title' => "{$category->name} | Kazi Fashion World",
        ]);
    }
}
