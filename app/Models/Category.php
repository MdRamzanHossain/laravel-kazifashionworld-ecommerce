<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Parent category relationship.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Child subcategories relationship.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Products belonging to this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope query to top-level parent categories only.
     */
    public function scopeParents(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope query to subcategories only.
     */
    public function scopeOnlySubcategories(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Check if this is a top-level category.
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this is a subcategory.
     */
    public function isSubcategory(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Get full hierarchical name (e.g. "Skin Care > Face Wash").
     */
    public function getHierarchyNameAttribute(): string
    {
        if ($this->parent) {
            return "{$this->parent->name} > {$this->name}";
        }

        return $this->name;
    }

    /**
     * Get direct URL to this category's dedicated page.
     */
    public function getUrlAttribute(): string
    {
        return route('category.show', $this->slug);
    }

    /**
     * Get array of IDs including this category and all its child subcategories.
     */
    public function getAllCategoryIds(): array
    {
        if ($this->isParent()) {
            return Category::where('id', $this->id)
                ->orWhere('parent_id', $this->id)
                ->pluck('id')
                ->toArray();
        }

        return [$this->id];
    }
}