<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EcommerceStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. Revenue Calculations
        $validOrdersQuery = Order::where('order_status', '!=', 'cancelled');
        $totalRevenue = (clone $validOrdersQuery)->sum('grand_total');

        $thisMonthRevenue = (clone $validOrdersQuery)->where('created_at', '>=', $startOfMonth)->sum('grand_total');
        $lastMonthRevenue = (clone $validOrdersQuery)->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->sum('grand_total');

        $revenueGrowth = $lastMonthRevenue > 0 
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) 
            : ($thisMonthRevenue > 0 ? 100 : 0);

        // 7-day revenue trend sparkline
        $revenueTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $revenueTrend[] = (float) (clone $validOrdersQuery)
                ->whereDate('created_at', $day->toDateString())
                ->sum('grand_total');
        }

        // 2. Order Metrics
        $totalOrdersCount = Order::count();
        $pendingOrdersCount = Order::where('order_status', 'pending')->count();
        $processingOrdersCount = Order::where('order_status', 'processing')->count();
        $deliveredOrdersCount = Order::where('order_status', 'delivered')->count();

        // 7-day order count sparkline
        $ordersTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $ordersTrend[] = Order::whereDate('created_at', $day->toDateString())->count();
        }

        // 3. Average Order Value (AOV)
        $aov = $totalOrdersCount > 0 ? ($totalRevenue / $totalOrdersCount) : 0;

        // 4. Customer Base & Repeat Rate
        $totalCustomers = User::where('role', 'customer')->count();
        $repeatCustomersCount = User::where('role', 'customer')
            ->has('orders', '>=', 2)
            ->count();
        $repeatRate = $totalCustomers > 0 ? round(($repeatCustomersCount / $totalCustomers) * 100, 1) : 0;

        // 5. Total Customer Savings (Discounts)
        $totalDiscounts = Order::sum('discount_amount');

        // 6. Inventory Alert (Stock <= 5)
        $lowStockCount = Product::where('stock_quantity', '<=', 5)->count();

        return [
            Stat::make('Total Net Revenue', 'BDT ' . number_format($totalRevenue, 2))
                ->description(($revenueGrowth >= 0 ? "+{$revenueGrowth}%" : "{$revenueGrowth}%") . ' vs last month')
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($revenueTrend)
                ->color($revenueGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('Total Orders', number_format($totalOrdersCount))
                ->description("{$pendingOrdersCount} Pending • {$processingOrdersCount} Processing • {$deliveredOrdersCount} Delivered")
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->chart($ordersTrend)
                ->color('primary'),

            Stat::make('Average Order Value (AOV)', 'BDT ' . number_format($aov, 2))
                ->description('Mean gross basket size')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('Customer Base', number_format($totalCustomers))
                ->description("{$repeatRate}% Repeat Buyers ({$repeatCustomersCount} customers)")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Promotional Discounts', 'BDT ' . number_format($totalDiscounts, 2))
                ->description('Total customer promo savings')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('warning'),

            Stat::make('Low Stock Items', number_format($lowStockCount))
                ->description($lowStockCount > 0 ? 'Items need urgent restock (≤5 units)' : 'All inventory levels healthy')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-badge')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),
        ];
    }
}
