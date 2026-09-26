<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlashSaleResource\Pages;
use App\Models\FlashSale;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FlashSaleResource extends Resource
{
    protected static ?string $model = FlashSale::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Sales & Discounts';

    protected static ?string $navigationLabel = 'Flash Sales & Deals';

    protected static ?string $title = 'Flash Sale Campaigns';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Campaign Details & Validity Schedule')
                    ->description('Set up the flash sale name, promotional tagline, and countdown timers.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Campaign Title')
                            ->placeholder('e.g. Mega Eid Flash Deals, Midnight Luxury Clearance')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Promotional Tagline / Subtitle')
                            ->placeholder('e.g. Up to 50% OFF on Top Designer Sharara & Couture')
                            ->maxLength(255),

                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('Flash Sale Start Time')
                            ->default(Carbon::now())
                            ->required()
                            ->native(false)
                            ->helperText('The countdown begins and prices activate from this timestamp.'),

                        Forms\Components\DateTimePicker::make('end_time')
                            ->label('Flash Sale End Time')
                            ->default(Carbon::now()->addDays(3))
                            ->required()
                            ->native(false)
                            ->helperText('The live countdown timer on the storefront ticks down to this timestamp.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Campaign Active & Published')
                            ->default(true)
                            ->required()
                            ->helperText('When enabled and within the time window, the flash sale countdown displays prominently on the storefront.'),

                        Forms\Components\FileUpload::make('banner_image')
                            ->label('Promotional Graphic / Banner (Optional)')
                            ->disk('public')
                            ->directory('flash_sales')
                            ->image()
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Flash Sale Products & Special Pricing')
                    ->description('Select participating products, set exclusive flash deal prices, and manage stock quotas.')
                    ->schema([
                        Forms\Components\Repeater::make('flashSaleProducts')
                            ->relationship('flashSaleProducts')
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Product')
                                    ->options(Product::where('is_active', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $product = Product::find($state);
                                            if ($product) {
                                                // Default flash price 20% lower than regular/sale price
                                                $basePrice = $product->sale_price ?: $product->price;
                                                $set('flash_price', round($basePrice * 0.8, 2));
                                            }
                                        }
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('flash_price')
                                    ->label('Flash Price (BDT)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('BDT')
                                    ->helperText('Special discounted price during flash sale.')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('quantity_limit')
                                    ->label('Flash Stock Cap')
                                    ->numeric()
                                    ->default(50)
                                    ->required()
                                    ->helperText('Max units for flash deal.')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('sold_count')
                                    ->label('Claimed / Sold')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->helperText('Units claimed.')
                                    ->columnSpan(1),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Include in Sale')
                                    ->default(true)
                                    ->columnSpan(1),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('+ Add Product to Flash Sale')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('banner_image')
                    ->label('Banner')
                    ->circular(false)
                    ->defaultImageUrl(url('images/logo.png')),

                Tables\Columns\TextColumn::make('title')
                    ->label('Campaign Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (FlashSale $record) => $record->subtitle),

                Tables\Columns\TextColumn::make('status')
                    ->label('Live Status')
                    ->badge()
                    ->getStateUsing(fn (FlashSale $record) => $record->status_label)
                    ->color(fn (FlashSale $record) => match ($record->status_label) {
                        'Live Now'          => 'success',
                        'Scheduled'         => 'warning',
                        'Expired'           => 'gray',
                        default             => 'danger',
                    }),

                Tables\Columns\TextColumn::make('flash_sale_products_count')
                    ->label('Deals Count')
                    ->counts('flashSaleProducts')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('End')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\Filter::make('live_now')
                    ->label('Live Now Only')
                    ->query(fn ($query) => $query->where('is_active', true)
                        ->where('start_time', '<=', Carbon::now())
                        ->where('end_time', '>=', Carbon::now())),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (FlashSale $record) => $record->is_active ? 'Pause' : 'Activate')
                    ->icon(fn (FlashSale $record) => $record->is_active ? 'heroicon-m-pause' : 'heroicon-m-play')
                    ->color(fn (FlashSale $record) => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (FlashSale $record) {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title('Flash Sale status updated!')
                            ->body("Campaign '{$record->title}' is now " . ($record->is_active ? 'Active' : 'Inactive') . ".")
                            ->success()
                            ->send();
                    }),
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
            'index'  => Pages\ListFlashSales::route('/'),
            'create' => Pages\CreateFlashSale::route('/create'),
            'edit'   => Pages\EditFlashSale::route('/{record}/edit'),
        ];
    }
}
