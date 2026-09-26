<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?string $heading = '⚡ Recent Orders Stream';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()->limit(6)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->badge()
                    ->color('primary')
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\ImageColumn::make('orderItems.product.image')
                    ->label('Items')
                    ->circular()
                    ->stacked()
                    ->limit(3),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->description(fn (Order $record): string => $record->customer_phone ?? $record->customer_email ?? '')
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Amount')
                    ->money('BDT')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state))
                    ->color(fn (Order $record): string => match ($record->payment_status) {
                        'paid'    => 'success',
                        'failed'  => 'danger',
                        default   => 'warning',
                    }),

                Tables\Columns\TextColumn::make('order_status')
                    ->label('Fulfillment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'shipped'    => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Placed At')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('manage')
                    ->label('Manage')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', $record->id)),
            ])
            ->paginated(false);
    }
}
