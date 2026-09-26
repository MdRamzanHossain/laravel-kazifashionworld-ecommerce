<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        \Filament\Facades\Filament::serving(function () {
            \Filament\Facades\Filament::registerNavigationItems([
                \Filament\Navigation\NavigationItem::make("File Manager")
                    ->url("/admin-fm.php")
                    ->icon("heroicon-o-folder")
                    ->group("Settings")
                    ->sort(99),
            ]);
        });
        
        Order::observe(OrderObserver::class);
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        \App\Models\ProductVariation::observe(\App\Observers\ProductVariationObserver::class);
        \App\Models\Review::observe(\App\Observers\ReviewObserver::class);
    }
}