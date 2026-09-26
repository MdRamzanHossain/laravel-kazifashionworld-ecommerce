<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource\Widgets\CouponStatsOverview;
use App\Models\Coupon;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Sales & Discounts';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Coupon Identity & Value')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Promo Code')
                            ->placeholder('e.g. EID2026, SAVE20')
                            ->required()
                            ->maxLength(50)
                            ->unique(Coupon::class, 'code', ignoreRecord: true)
                            ->dehydrateStateUsing(fn ($state) => strtoupper(trim($state)))
                            ->helperText('Promo codes are automatically normalized to uppercase.'),

                        Forms\Components\TextInput::make('description')
                            ->label('Description / Offer Tagline')
                            ->placeholder('e.g. Eid 2026 Special BDT 500 Discount')
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Discount Type')
                            ->options([
                                'fixed'      => 'Fixed Amount Discount (BDT)',
                                'percentage' => 'Percentage Discount (%)',
                            ])
                            ->default('fixed')
                            ->required()
                            ->reactive()
                            ->native(false),

                        Forms\Components\TextInput::make('value')
                            ->label(fn (callable $get) => $get('type') === 'percentage' ? 'Discount Percentage (%)' : 'Discount Amount (BDT)')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->prefix(fn (callable $get) => $get('type') === 'percentage' ? '%' : 'BDT'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active & Usable by Customers')
                            ->default(true)
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Spend Thresholds & Usage Limits')
                    ->schema([
                        Forms\Components\TextInput::make('min_spend')
                            ->label('Minimum Cart Subtotal (BDT)')
                            ->numeric()
                            ->default(0)
                            ->prefix('BDT')
                            ->helperText('Minimum cart value required before this discount can be applied. (0 = no minimum)'),

                        Forms\Components\TextInput::make('max_discount')
                            ->label('Maximum Discount Cap (BDT)')
                            ->numeric()
                            ->prefix('BDT')
                            ->visible(fn (callable $get) => $get('type') === 'percentage')
                            ->helperText('Limits the maximum monetary discount for percentage coupons (e.g. max BDT 1,000).'),

                        Forms\Components\TextInput::make('usage_limit')
                            ->label('Total Storewide Usage Limit')
                            ->numeric()
                            ->placeholder('Unlimited')
                            ->helperText('Total number of times this coupon can be redeemed across the store.'),

                        Forms\Components\TextInput::make('usage_limit_per_user')
                            ->label('Limit Per Customer / Phone')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->helperText('Number of times an individual customer can use this coupon.'),
                    ])->columns(2),

                Forms\Components\Section::make('Validity Schedule')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Start Date & Time')
                            ->native(false)
                            ->helperText('Optional start timestamp when the coupon becomes active.'),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expiry Date & Time')
                            ->native(false)
                            ->helperText('Optional expiration timestamp after which the coupon is disabled.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Promo Code')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('formatted_discount')
                    ->label('Discount')
                    ->badge()
                    ->color('success')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('min_spend')
                    ->label('Min Spend')
                    ->money('BDT')
                    ->sortable(),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('Redemptions')
                    ->formatStateUsing(fn (Coupon $record) => $record->used_count . ($record->usage_limit ? ' / ' . $record->usage_limit : ' (Unlimited)'))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Validity Status')
                    ->formatStateUsing(function (Coupon $record) {
                        if ($record->isExpired()) {
                            return 'Expired (' . $record->expires_at->format('M d, Y') . ')';
                        }
                        if ($record->hasNotStarted()) {
                            return 'Starts ' . $record->starts_at->format('M d, Y');
                        }
                        if ($record->expires_at) {
                            return 'Expires ' . $record->expires_at->diffForHumans();
                        }
                        return 'Never Expires';
                    })
                    ->badge()
                    ->color(fn (Coupon $record) => $record->isExpired() ? 'danger' : ($record->hasNotStarted() ? 'warning' : 'success'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'fixed'      => 'Fixed Amount',
                        'percentage' => 'Percentage',
                    ]),

                Tables\Filters\Filter::make('active_now')
                    ->label('Currently Valid & Active')
                    ->query(fn ($query) => $query->where('is_active', true)
                        ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', Carbon::now()))
                        ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now()))),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn (Coupon $record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (Coupon $record) => $record->is_active ? 'heroicon-m-no-symbol' : 'heroicon-m-check-circle')
                    ->color(fn (Coupon $record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Coupon $record) {
                        $record->update(['is_active' => !$record->is_active]);
                        Notification::make()
                            ->title('Coupon status updated!')
                            ->body("Promo code '{$record->code}' is now " . ($record->is_active ? 'Active' : 'Inactive') . ".")
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

    public static function getWidgets(): array
    {
        return [
            CouponStatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
