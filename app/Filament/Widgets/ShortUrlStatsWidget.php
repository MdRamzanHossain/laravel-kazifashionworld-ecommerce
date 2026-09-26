<?php

namespace App\Filament\Widgets;

use App\Models\ShortUrl;
use App\Models\ShortUrlClick;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShortUrlStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalLinks = ShortUrl::count();
        $totalClicks = (int) ShortUrl::sum('clicks');
        $clickedLinks = ShortUrl::where('clicks', '>', 0)->count();
        $ctr = $totalLinks > 0 ? round(($clickedLinks / $totalLinks) * 100, 1) : 0;

        $topLink = ShortUrl::with('order')->orderByDesc('clicks')->first();
        $topLinkDescription = $topLink && $topLink->clicks > 0
            ? "Code: {$topLink->code} ({$topLink->clicks} clicks)"
            : 'No clicks yet';

        return [
            Stat::make('Total SMS Links', number_format($totalLinks))
                ->description('Generated tracking short links')
                ->descriptionIcon('heroicon-m-link')
                ->color('primary'),

            Stat::make('Total Link Clicks', number_format($totalClicks))
                ->description('Total visits to tracking URLs')
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('success'),

            Stat::make('Click-Through Rate (CTR)', "{$ctr}%")
                ->description("{$clickedLinks} of {$totalLinks} links engaged")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($ctr >= 50 ? 'success' : ($ctr > 0 ? 'warning' : 'gray')),

            Stat::make('Top Performing Link', $topLinkDescription)
                ->description($topLink?->order ? "Order #{$topLink->order->order_number}" : 'Overall leading link')
                ->descriptionIcon('heroicon-m-trophy')
                ->color('warning'),
        ];
    }
}
