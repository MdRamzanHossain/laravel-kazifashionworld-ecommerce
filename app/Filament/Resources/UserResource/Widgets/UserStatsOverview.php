<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $totalCustomers = User::where('role', 'customer')->orWhereNull('role')->count();
        $totalStaff = User::whereIn('role', ['admin', 'manager'])->count();
        $activeUsers = User::where('is_active', true)->count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description('All registered system accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Customers', $totalCustomers)
                ->description('Storefront shopping accounts')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),

            Stat::make('Admins & Staff', $totalStaff)
                ->description('Panel management roles')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('danger'),

            Stat::make('Active Status', $activeUsers . ' / ' . $totalUsers)
                ->description('Accounts permitted to login')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }
}
