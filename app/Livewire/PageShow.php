<?php

namespace App\Livewire;

use App\Models\Page;
use Livewire\Component;

class PageShow extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = trim($slug, '/');
    }

    public function render()
    {
        $cleanSlug = trim($this->slug, '/');

        $page = Page::where(function ($query) use ($cleanSlug) {
                $query->where('slug', $cleanSlug)
                      ->orWhere('slug', '/' . $cleanSlug)
                      ->orWhere('slug', $cleanSlug . '/')
                      ->orWhere('slug', '/' . $cleanSlug . '/')
                      ->orWhere('slug', $this->slug);
            })
            ->where('is_active', true)
            ->firstOrFail();

        $otherPages = Page::where('is_active', true)
            ->where('id', '!=', $page->id)
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('livewire.page-show', [
            'page'       => $page,
            'otherPages' => $otherPages,
        ])->layout('components.layouts.app', [
            'title' => $page->meta_title ?: "{$page->title} | Kazi Fashion World",
        ]);
    }
}
