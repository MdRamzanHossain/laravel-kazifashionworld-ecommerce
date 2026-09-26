<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShortUrlResource\Pages;
use App\Models\ShortUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShortUrlResource extends Resource
{
    protected static ?string $model = ShortUrl::class;

    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-rays';

    protected static ?string $navigationGroup = 'Reports & Logs';

    protected static ?string $navigationLabel = 'SMS Link Tracking';

    protected static ?string $modelLabel = 'Short Link';

    protected static ?string $pluralModelLabel = 'SMS Link Clicks & Tracking';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Link Details')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('clicks')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('destination_url')
                            ->columnSpanFull()
                            ->url()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Short Link Overview')
                    ->schema([
                        Infolists\Components\TextEntry::make('code')
                            ->label('Short Code')
                            ->badge()
                            ->color('primary')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('clicks')
                            ->label('Total Clicks')
                            ->badge()
                            ->color(fn (int $state): string => match (true) {
                                $state === 0 => 'gray',
                                $state < 5   => 'warning',
                                default      => 'success',
                            }),
                        Infolists\Components\TextEntry::make('order.order_number')
                            ->label('Associated Order #')
                            ->placeholder('None'),
                        Infolists\Components\TextEntry::make('order.customer_name')
                            ->label('Customer')
                            ->placeholder('None'),
                        Infolists\Components\TextEntry::make('destination_url')
                            ->label('Full Destination URL')
                            ->columnSpanFull()
                            ->copyable()
                            ->url(fn (ShortUrl $record): string => $record->destination_url, true),
                    ])->columns(2),

                Infolists\Components\Section::make('Click History Logs')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('clicksHistory')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('clicked_at')
                                    ->label('Time')
                                    ->dateTime('M d, Y H:i:s'),
                                Infolists\Components\TextEntry::make('device_type')
                                    ->label('Device')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'Mobile'  => 'info',
                                        'Tablet'  => 'warning',
                                        default   => 'gray',
                                    }),
                                Infolists\Components\TextEntry::make('ip_address')
                                    ->label('IP Address'),
                                Infolists\Components\TextEntry::make('user_agent')
                                    ->label('User Agent')
                                    ->limit(60)
                                    ->tooltip(fn (?string $state): ?string => $state),
                            ])->columns(4),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Short Link')
                    ->badge()
                    ->color('primary')
                    ->copyable()
                    ->copyMessage('Short URL copied')
                    ->url(fn (ShortUrl $record): string => $record->short_url, true)
                    ->tooltip('Click to open in new tab')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('order.customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('order.customer_phone')
                    ->label('Phone')
                    ->searchable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('destination_url')
                    ->label('Destination')
                    ->limit(35)
                    ->tooltip(fn (ShortUrl $record): string => $record->destination_url)
                    ->url(fn (ShortUrl $record): string => $record->destination_url, true)
                    ->searchable(),

                Tables\Columns\TextColumn::make('clicks')
                    ->label('Total Clicks')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state < 5   => 'warning',
                        default      => 'success',
                    })
                    ->icon(fn (int $state): string => $state > 0 ? 'heroicon-m-cursor-arrow-rays' : 'heroicon-m-eye-slash')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Activity')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('has_clicks')
                    ->label('Clicked Links Only')
                    ->query(fn ($query) => $query->where('clicks', '>', 0)),

                Tables\Filters\Filter::make('zero_clicks')
                    ->label('Unclicked Links')
                    ->query(fn ($query) => $query->where('clicks', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('test_click')
                    ->label('Test Open')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn (ShortUrl $record): string => $record->short_url, true),
                Tables\Actions\Action::make('reset_clicks')
                    ->label('Reset')
                    ->icon('heroicon-m-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (ShortUrl $record) {
                        $record->update(['clicks' => 0]);
                        $record->clicksHistory()->delete();
                        Notification::make()
                            ->title('Clicks reset successfully')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListShortUrls::route('/'),
        ];
    }
}
