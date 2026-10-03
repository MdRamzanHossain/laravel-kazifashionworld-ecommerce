<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active'        => 'boolean',
        'show_in_footer'   => 'boolean',
        'show_in_header'   => 'boolean',
        'is_full_width'    => 'boolean',
        'hide_header_hero' => 'boolean',
        'sort_order'       => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($page) {
            if (!empty($page->slug)) {
                $page->slug = trim($page->slug, '/');
            } elseif (empty($page->slug) && !empty($page->title)) {
                $page->slug = Str::slug($page->title);
            }

            // Ensure custom fields don't throw 500 if columns are not migrated yet
            foreach (['is_full_width', 'hide_header_hero', 'custom_css', 'custom_js'] as $field) {
                if (isset($page->attributes[$field]) && !\Illuminate\Support\Facades\Schema::hasColumn('pages', $field)) {
                    unset($page->attributes[$field]);
                }
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
        return url('/' . ltrim($this->slug ?? '', '/'));
    }
}
