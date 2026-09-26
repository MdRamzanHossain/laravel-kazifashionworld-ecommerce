<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderNotificationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    public ?string $previousStatus = null;

    protected function beforeFill(): void
    {
        $this->previousStatus = $this->record->order_status;
    }

    protected function beforeSave(): void
    {
        // Store initial status before saving changes
        $this->previousStatus = $this->record->getOriginal('order_status');
    }

    protected function afterSave(): void
    {
        /** @var Order $order */
        $order = $this->record;
        $newStatus = $order->order_status;
        $prevStatus = $this->previousStatus ?? $order->getOriginal('order_status');

        // If order status has changed, dispatch customer Email and SMS
        if ($prevStatus !== $newStatus) {
            OrderNotificationService::notifyCustomerStatusUpdated($order, $prevStatus, $newStatus);

            Notification::make()
                ->title('Customer Notified!')
                ->body("Order #{$order->order_number} status updated to '" . ucfirst($newStatus) . "'. Email and SMS alerts dispatched.")
                ->success()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            // Resend Order Confirmation Email
            Actions\Action::make('resend_email')
                ->label('Resend Confirmation Email')
                ->icon('heroicon-m-envelope')
                ->color('info')
                ->requiresConfirmation()
                ->action(function (Order $record) {
                    OrderNotificationService::notifyCustomerOrderPlaced($record);
                    Notification::make()
                        ->title('Email Sent')
                        ->body("Confirmation email resent to {$record->customer_email}.")
                        ->success()
                        ->send();
                }),

            // Resend SMS Tracking Alert
            Actions\Action::make('resend_sms')
                ->label('Resend SMS Alert')
                ->icon('heroicon-m-chat-bubble-left-right')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (Order $record) {
                    OrderNotificationService::notifyCustomerOrderPlaced($record);
                    Notification::make()
                        ->title('SMS Sent')
                        ->body("Tracking SMS resent to {$record->customer_phone}.")
                        ->success()
                        ->send();
                }),

            // Send Custom Customer SMS
            Actions\Action::make('custom_sms')
                ->label('Send Custom SMS')
                ->icon('heroicon-m-paper-airplane')
                ->color('warning')
                ->form([
                    Forms\Components\Textarea::make('message')
                        ->label('Custom Message to Customer')
                        ->placeholder('Dear customer, your order...')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (Order $record, array $data) {
                    OrderNotificationService::sendCustomSms($record, $data['message']);
                    Notification::make()
                        ->title('Custom SMS Sent')
                        ->body("Message sent to {$record->customer_phone}.")
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
