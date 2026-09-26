<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\FileUpload;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Product & Catalog Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('category_id')
                    ->label('Category / Subcategory')
                    ->options(function () {
                        return Category::with('children')
                            ->whereNull('parent_id')
                            ->get()
                            ->mapWithKeys(function ($parent) {
                                $options = [$parent->id => "📁 {$parent->name} (Main Category)"];
                                foreach ($parent->children as $child) {
                                    $options[$child->id] = "↳ {$child->name}";
                                }
                                return [$parent->name => $options];
                            });
                    })
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Product::class, 'slug', ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'This slug has already been taken. Please choose a different title or slug.',
                    ]),

                // Main Featured Image (4:5 Ratio)
                FileUpload::make('image')
                    ->label('Main Featured Image (4:5 Ratio - 800x1000)')
                    ->image()
                    ->imageEditor()
                    ->directory('products')
                    ->imageCropAspectRatio('4:5')
                    ->imageResizeTargetWidth('800')
                    ->imageResizeTargetHeight('1000')
                    ->disk('public')
                    ->visibility('public')
                    ->columnSpanFull(),

                // Gallery Images (Multiple - 4:5 Ratio)
                FileUpload::make('images')
                    ->label('Product Gallery Images (4:5 Ratio - 800x1000)')
                    ->image()
                    ->imageEditor()
                    ->directory('products/gallery')
                    ->imageCropAspectRatio('4:5')
                    ->imageResizeTargetWidth('800')
                    ->imageResizeTargetHeight('1000')
                    ->disk('public')
                    ->visibility('public')
                    ->multiple()
                    ->reorderable(),

                Forms\Components\Textarea::make('short_description')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('BDT'),

                Forms\Components\TextInput::make('sale_price')
                    ->numeric()
                    ->prefix('BDT')
                    ->default(null),

                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->required()
                    ->maxLength(255)
                    ->unique(Product::class, 'sku', ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'This SKU is already used by another product. Please use a unique SKU.',
                    ])
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('generateSku')
                            ->icon('heroicon-m-arrow-path')
                            ->tooltip('Generate Unique SKU')
                            ->action(function (Forms\Set $set) {
                                $set('sku', 'KAZI-' . strtoupper(Str::random(6)));
                            })
                    ),

                Forms\Components\TextInput::make('stock_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->required(),

                Forms\Components\Toggle::make('is_featured')
                    ->default(false)
                    ->required(),

                Forms\Components\Select::make('crossSells')
                    ->label('Frequently Bought Together (Cross-Sells)')
                    ->relationship('crossSells', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                    
                Forms\Components\Section::make('Premium Product Variations')
                    ->description('Define your product options (Colors, Sizes). You can add images and price overrides directly inside the Color options!')
                    ->schema([
                        Forms\Components\Toggle::make('has_variations')
                            ->label('This product has multiple variations')
                            ->live()
                            ->default(false),
                            
                        Forms\Components\Repeater::make('attributes_schema')
                            ->label('Attributes & Swatches')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Attribute Name (e.g. Color, Size)')
                                        ->required(),
                                    Forms\Components\Select::make('type')
                                        ->label('Display Style')
                                        ->options([
                                            'button' => 'Text Buttons (Best for Sizes)',
                                            'color' => 'Visual Swatch & Image (Best for Colors)',
                                        ])
                                        ->default('button')
                                        ->required()
                                        ->live(),
                                ]),
                                Forms\Components\Repeater::make('options')
                                    ->label('Options')
                                    ->schema([
                                        Forms\Components\Grid::make(4)->schema([
                                            Forms\Components\TextInput::make('label')
                                                ->label('Label (e.g. XL, Red)')
                                                ->required()
                                                ->columnSpan(fn (Forms\Get $get) => $get('../../type') === 'color' ? 1 : 4),
                                            
                                            Forms\Components\ColorPicker::make('color_code')
                                                ->label('Color Code')
                                                ->visible(fn (Forms\Get $get) => $get('../../type') === 'color')
                                                ->columnSpan(1),
                                                
                                            Forms\Components\TextInput::make('price')
                                                ->label('Extra Price (+ BDT)')
                                                ->numeric()
                                                ->nullable()
                                                ->columnSpan(1)
                                                ->visible(fn (Forms\Get $get) => $get('../../type') === 'color'),
                                                
                                            Forms\Components\FileUpload::make('image')
                                                ->label('Option Image')
                                                ->image()
                                                ->directory('products/options')
                                                ->visible(fn (Forms\Get $get) => $get('../../type') === 'color')
                                                ->columnSpan(1),
                                        ])
                                    ])
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->visible(fn (Forms\Get $get) => $get('has_variations') === true)
                            ->columnSpanFull(),
                            
                        Forms\Components\Repeater::make('variations')
                            ->label('Advanced Overrides (Optional)')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('variation_name')
                                    ->label('Variation Name (e.g. Red - XL)')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('price')
                                    ->label('Base Price (Override)')
                                    ->numeric()
                                    ->prefix('BDT'),
                                Forms\Components\TextInput::make('sale_price')
                                    ->label('Sale Price (Override)')
                                    ->numeric()
                                    ->prefix('BDT'),
                                Forms\Components\TextInput::make('stock_quantity')
                                    ->label('Stock Quantity')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                Forms\Components\FileUpload::make('image')
                                    ->label('Variation Image')
                                    ->image()
                                    ->directory('products'),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['variation_name'] ?? null)
                            ->visible(fn (Forms\Get $get) => $get('has_variations') === true)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Photo')
                    ->disk('public')
                    ->visibility('public')
                    ->height(50)
                    ->extraImgAttributes(['class' => 'aspect-[4/5] object-cover rounded-lg shadow-xs'])
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->formatStateUsing(function (Product $record) {
                        if ($record->category && $record->category->parent) {
                            return "{$record->category->parent->name} > {$record->category->name}";
                        }
                        return $record->category?->name ?? '-';
                    })
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('BDT')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sale_price')
                    ->money('BDT')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),

                Tables\Columns\IconColumn::make('has_variations')
                    ->boolean()
                    ->label('Variants'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Filter by Category')
                    ->options(function () {
                        return Category::with('children')
                            ->whereNull('parent_id')
                            ->get()
                            ->mapWithKeys(function ($parent) {
                                $options = [$parent->id => "📁 {$parent->name}"];
                                foreach ($parent->children as $child) {
                                    $options[$child->id] = "↳ {$child->name}";
                                }
                                return [$parent->name => $options];
                            });
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}