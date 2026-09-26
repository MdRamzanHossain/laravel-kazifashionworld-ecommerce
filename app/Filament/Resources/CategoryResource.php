<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Product & Catalog Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Hierarchy & Information')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('Parent Category')
                            ->placeholder('None (Main Parent Category)')
                            ->options(function (?Category $record) {
                                return Category::whereNull('parent_id')
                                    ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('Select a parent category if this is a Subcategory. Leave empty for a top-level Main Category.'),

                        Forms\Components\TextInput::make('name')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Category::class, 'slug', ignoreRecord: true),

                        Forms\Components\TextInput::make('hero_badge')
                            ->label('Hero Glowing Badge')
                            ->placeholder('e.g. 🌸 2026 Festive Couture, ✨ Authentic Korean Skincare')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Category Description / Editorial Subtitle')
                            ->rows(2)
                            ->placeholder('e.g. Discover handcrafted couture attire and authentic dermatological formulas curated for elegance.')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('image')
                            ->label('Thumbnail Icon / Story Image')
                            ->image()
                            ->imageEditor()
                            ->deletable(true)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('categories')
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('banner_image')
                            ->label('Hero Background Banner (Optional)')
                            ->image()
                            ->imageEditor()
                            ->deletable(true)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('categories/banners')
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->visibility('public')
                    ->circular()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Type / Parent')
                    ->badge()
                    ->color(fn (?string $state): string => $state ? 'info' : 'gray')
                    ->formatStateUsing(fn (?string $state): string => $state ? "Subcategory of {$state}" : 'Main Category')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('children_count')
                    ->counts('children')
                    ->label('Subcategories')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Products')
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Filter by Parent Category')
                    ->options(fn () => Category::whereNull('parent_id')->pluck('name', 'id')),

                Tables\Filters\Filter::make('main_categories')
                    ->label('Main Categories Only')
                    ->query(fn ($query) => $query->whereNull('parent_id')),

                Tables\Filters\Filter::make('subcategories')
                    ->label('Subcategories Only')
                    ->query(fn ($query) => $query->whereNotNull('parent_id')),
            ])
            ->actions([
                Tables\Actions\Action::make('view_live')
                    ->label('View Page')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Category $record) => route('category.show', $record->slug))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}