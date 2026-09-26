<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopSellingProductsWidget extends BaseWidget
{
    protected static ?string $heading = '🔥 Top-Selling Products by Revenue';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->with('category')
                    ->withSum('orderItems as total_units_sold', 'quantity')
                    ->withSum('orderItems as total_sales_revenue', 'total')
                    ->orderByDesc('total_sales_revenue')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Thumbnail')
                    ->circular()
                    ->defaultImageUrl(asset('images/logo.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Product Name')
                    ->weight('bold')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('total_units_sold')
                    ->label('Units Sold')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0) . ' sold')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_sales_revenue')
                    ->label('Revenue Generated')
                    ->money('BDT')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Stock Status')
                    ->formatStateUsing(function (Product $record) {
                        if ($record->stock_quantity <= 0) {
                            return 'Out of Stock';
                        }
                        if ($record->stock_quantity <= 5) {
                            return "Low Stock ({$record->stock_quantity} left)";
                        }
                        return "In Stock ({$record->stock_quantity})";
                    })
                    ->badge()
                    ->color(fn (Product $record) => $record->stock_quantity <= 0 ? 'danger' : ($record->stock_quantity <= 5 ? 'warning' : 'success')),
            ])
            ->actions([
                Tables\Actions\Action::make('view_product')
                    ->label('Edit Product')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (Product $record): string => route('filament.admin.resources.products.edit', $record->id)),
            ])
            ->paginated(false);
    }
}
