<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Shop Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->disabled()
                            ->required(),
                        Forms\Components\Select::make('order_status')
                            ->label('Order Status')
                            ->options([
                                'pending'    => 'Pending',
                                'processing' => 'Processing',
                                'shipped'    => 'Shipped',
                                'delivered'  => 'Delivered',
                                'cancelled'  => 'Cancelled',
                            ])
                            ->required()
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Payment & Gateway Information')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cod'    => 'Cash on Delivery (COD)',
                                'online' => 'Online Payment (Gateway)',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_gateway')
                            ->label('Payment Gateway')
                            ->options([
                                'sslcommerz' => 'SSLCommerz (bKash/Nagad/Cards)',
                                'bkash'      => 'bKash PGW',
                                'stripe'     => 'Stripe',
                            ])
                            ->placeholder('None (COD)'),
                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid'    => 'Paid',
                                'failed'  => 'Failed',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('Gateway Transaction ID')
                            ->placeholder('e.g. SSLCZ_... / Bank Ref')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('shipping_fee')
                            ->label('Shipping Fee')
                            ->numeric()
                            ->prefix('BDT')
                            ->default(80.00),
                        Forms\Components\TextInput::make('grand_total')
                            ->label('Grand Total')
                            ->numeric()
                            ->prefix('BDT')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Courier & Shipment Tracking')
                    ->description('Provide courier partner and live tracking link for the customer.')
                    ->schema([
                        Forms\Components\Select::make('courier_name')
                            ->label('Courier Partner')
                            ->options([
                                'Steadfast'    => 'Steadfast Courier',
                                'Pathao'       => 'Pathao Courier',
                                'RedX'         => 'RedX Delivery',
                                'Paperfly'     => 'Paperfly',
                                'eCourier'     => 'eCourier',
                                'Sundarban'    => 'Sundarban Courier Service',
                                'SA Paribahan' => 'SA Paribahan',
                                'DHL'          => 'DHL Express',
                                'FedEx'        => 'FedEx',
                                'Other'        => 'Other / Custom',
                            ])
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Tracking / Consignment ID')
                            ->placeholder('e.g. ST-987654321 / CID-123456')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tracking_url')
                            ->label('Direct Courier Tracking URL')
                            ->placeholder('https://steadfast.com.bd/tracking/...')
                            ->url()
                            ->maxLength(1000)
                            ->suffixIcon('heroicon-m-arrow-top-right-on-square')
                            ->columnSpanFull(),
                        Forms\Components\Placeholder::make('sms_tracking_link')
                            ->label('SMS Short Tracking Link')
                            ->content(function (?Order $record): string {
                                if (!$record) return 'Will be generated upon saving.';
                                $shortUrl = $record->shortUrls()->latest()->first();
                                if (!$shortUrl) return 'No SMS link generated yet.';
                                return "{$shortUrl->short_url} ({$shortUrl->clicks} clicks received)";
                            }),
                    ])->columns(2),

                Forms\Components\Section::make('Customer & Shipping Details')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('customer_email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('customer_phone')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('district')
                            ->default('Dhaka')
                            ->required(),
                        Forms\Components\Textarea::make('shipping_address')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('orderItems')
                            ->relationship()
                            ->schema([
                                Forms\Components\Placeholder::make('product_image')
                                    ->label('Thumbnail')
                                    ->content(function ($record) {
                                        if ($record && $record->product && $record->product->image) {
                                            return new \Illuminate\Support\HtmlString('<img src="' . asset('storage/' . $record->product->image) . '" style="height: 60px; border-radius: 8px;">');
                                        }
                                        return '-';
                                    }),
                                Forms\Components\Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->required()
                                    ->disabled(),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->required()
                                    ->disabled(),
                                Forms\Components\TextInput::make('unit_price')
                                    ->numeric()
                                    ->disabled()
                                    ->prefix('BDT'),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Order Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\ImageColumn::make('orderItems.product.image')
                    ->label('Items')
                    ->circular()
                    ->stacked()
                    ->limit(3),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_gateway')
                    ->label('Gateway')
                    ->badge()
                    ->color('info')
                    ->placeholder('COD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Tran ID')
                    ->placeholder('-')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('courier_name')
                    ->label('Courier')
                    ->badge()
                    ->color('info')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tracking_number')
                    ->label('Tracking #')
                    ->copyable()
                    ->url(fn (Order $record): ?string => $record->tracking_url, true)
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('short_link_clicks')
                    ->label('SMS Clicks')
                    ->state(fn (Order $record): int => (int) $record->shortUrls()->sum('clicks'))
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state < 5   => 'warning',
                        default      => 'success',
                    })
                    ->icon('heroicon-m-cursor-arrow-rays'),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('BDT')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_status')
                    ->label('Order Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'shipped'    => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending'    => 'heroicon-m-clock',
                        'processing' => 'heroicon-m-arrow-path',
                        'shipped'    => 'heroicon-m-truck',
                        'delivered'  => 'heroicon-m-check-badge',
                        'cancelled'  => 'heroicon-m-x-circle',
                        default      => 'heroicon-m-question-mark-circle',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'    => 'success',
                        'failed'  => 'danger',
                        default   => 'warning',
                    })
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('order_status')
                    ->options([
                        'pending'    => 'Pending',
                        'processing' => 'Processing',
                        'shipped'    => 'Shipped',
                        'delivered'  => 'Delivered',
                        'cancelled'  => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid'    => 'Paid',
                        'failed'  => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('payment_gateway')
                    ->options([
                        'sslcommerz' => 'SSLCommerz',
                        'bkash'      => 'bKash',
                        'stripe'     => 'Stripe',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('resend_email')
                        ->label('Resend Confirmation Email')
                        ->icon('heroicon-m-envelope')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Order $record) {
                            \App\Services\OrderNotificationService::notifyCustomerOrderPlaced($record);
                            \Filament\Notifications\Notification::make()
                                ->title('Email Sent')
                                ->body("Order confirmation resent to {$record->customer_email}.")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('resend_sms')
                        ->label('Resend Tracking SMS')
                        ->icon('heroicon-m-chat-bubble-left-right')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Order $record) {
                            \App\Services\OrderNotificationService::notifyCustomerOrderPlaced($record);
                            \Filament\Notifications\Notification::make()
                                ->title('SMS Sent')
                                ->body("Tracking SMS resent to {$record->customer_phone}.")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
