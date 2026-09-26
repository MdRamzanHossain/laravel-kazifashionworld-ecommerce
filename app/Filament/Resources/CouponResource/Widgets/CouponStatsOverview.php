<?php

namespace App\Filament\Resources\CouponResource\Widgets;

use App\Models\Coupon;
use App\Models\CouponUsage;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CouponStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $activeCoupons = Coupon::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', Carbon::now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now()))
            ->count();

        $totalRedemptions = CouponUsage::count();
        $totalSavings = CouponUsage::sum('discount_amount');
        $avgDiscount = $totalRedemptions > 0 ? ($totalSavings / $totalRedemptions) : 0;

        return [
            Stat::make('Active Promo Codes', $activeCoupons)
                ->description('Coupons currently valid for checkout')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make('Total Redemptions', $totalRedemptions)
                ->description('Orders placed with discounts')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Customer Savings', 'BDT ' . number_format($totalSavings, 2))
                ->description('Total discount given to customers')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Avg Discount / Order', 'BDT ' . number_format($avgDiscount, 2))
                ->description('Mean promotional markdown')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }
}
