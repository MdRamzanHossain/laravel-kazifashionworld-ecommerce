<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active'      => 'boolean',
        'show_in_footer' => 'boolean',
        'show_in_header' => 'boolean',
        'sort_order'     => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInFooter($query)
    {
        return $query->where('show_in_footer', true)->orderBy('sort_order', 'asc');
    }

    public function getUrlAttribute(): string
    {
        return route('page.show', $this->slug);
    }
}
