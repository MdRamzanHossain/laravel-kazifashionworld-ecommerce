<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlertWidget extends BaseWidget
{
    protected static ?string $heading = '⚠️ Low Stock & Restock Alerts (≤ 5 units remaining)';

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with('category')
                    ->where('stock_quantity', '<=', 5)
                    ->orderBy('stock_quantity', 'asc')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Thumbnail')
                    ->circular()
                    ->defaultImageUrl(asset('images/logo.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Selling Price')
                    ->money('BDT'),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Units Remaining')
                    ->formatStateUsing(function (int $state) {
                        return $state <= 0 ? 'Out of Stock (0)' : "{$state} units left";
                    })
                    ->badge()
                    ->color(fn (int $state) => $state <= 0 ? 'danger' : 'warning')
                    ->weight('bold'),
            ])
            ->actions([
                Tables\Actions\Action::make('restock')
                    ->label('Restock / Edit')
                    ->icon('heroicon-m-plus-circle')
                    ->color('success')
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.edit', $record->id)),
            ])
            ->emptyStateHeading('🎉 All products have healthy stock levels!')
            ->emptyStateDescription('No products currently have stock below 5 units.')
            ->emptyStateIcon('heroicon-o-check-badge')
            ->paginated(false);
    }
}
