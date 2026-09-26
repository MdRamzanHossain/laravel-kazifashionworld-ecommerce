<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsLogResource\Pages;
use App\Jobs\SendOrderSmsJob;
use App\Models\SmsLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class SmsLogResource extends Resource
{
    protected static ?string $model = SmsLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?string $navigationLabel = 'SMS Logs';

    protected static ?string $navigationGroup = 'Reports & Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('recipient')
                    ->disabled()
                    ->required(),
                Forms\Components\TextInput::make('gateway')
                    ->disabled(),
                Forms\Components\TextInput::make('status')
                    ->disabled(),
                Forms\Components\TextInput::make('attempts')
                    ->numeric()
                    ->disabled(),
                Forms\Components\DateTimePicker::make('sent_at')
                    ->disabled(),
                Forms\Components\Textarea::make('message')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('error_message')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('response_data')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('recipient')
                    ->label('Phone')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order #')
                    ->searchable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('gateway')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('message')
                    ->limit(35)
                    ->tooltip(fn (SmsLog $record): string => $record->message),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent'    => 'success',
                        'failed'  => 'danger',
                        default   => 'warning',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'sent'    => 'heroicon-m-check-circle',
                        'failed'  => 'heroicon-m-x-circle',
                        default   => 'heroicon-m-clock',
                    }),

                Tables\Columns\TextColumn::make('attempts')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(25)
                    ->tooltip(fn (SmsLog $record): ?string => $record->error_message)
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent'    => 'Sent',
                        'failed'  => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('gateway')
                    ->options([
                        'local_sms' => 'Local SMS Gateway',
                        'twilio'    => 'Twilio',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('retry')
                    ->label('Retry SMS')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (SmsLog $record): bool => $record->status === 'failed')
                    ->requiresConfirmation()
                    ->modalHeading('Retry Failed SMS')
                    ->modalDescription('Are you sure you want to re-queue this SMS for automatic retry?')
                    ->action(function (SmsLog $record) {
                        $record->update([
                            'status'        => 'pending',
                            'error_message' => null,
                        ]);

                        SendOrderSmsJob::dispatch($record);

                        Notification::make()
                            ->title('SMS Re-queued')
                            ->body("SMS to {$record->recipient} has been dispatched to the queue.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('retryBulk')
                        ->label('Retry Selected Failed SMS')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $retriedCount = 0;

                            foreach ($records as $record) {
                                if ($record->status === 'failed') {
                                    $record->update([
                                        'status'        => 'pending',
                                        'error_message' => null,
                                    ]);

                                    SendOrderSmsJob::dispatch($record);
                                    $retriedCount++;
                                }
                            }

                            Notification::make()
                                ->title('SMS Retries Dispatched')
                                ->body("{$retriedCount} failed SMS have been re-queued for delivery.")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsLogs::route('/'),
        ];
    }
}
