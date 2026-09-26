<?php

namespace App\Services;

use App\Jobs\SendOrderSmsJob;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\User;
use Exception;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\DatabaseNotification;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderNotificationService
{
    /**
     * Send in-app notification & optional SMS to all store Admins immediately.
     */
    public static function notifyAdminsNewOrder(Order $order): void
    {
        // 1. In-App Filament Notification
        try {
            $admins = User::whereIn('role', ['admin', 'manager'])->get();

            if ($admins->isNotEmpty()) {
                $totalFormatted = 'BDT ' . number_format($order->grand_total ?? $order->total_amount, 2);
                $paymentMethod = strtoupper($order->payment_method ?? 'COD');

                $notification = Notification::make()
                    ->title("🛍️ New Order Received: #{$order->order_number}")
                    ->body("Customer: {$order->customer_name} • {$totalFormatted} via {$paymentMethod}")
                    ->icon('heroicon-o-shopping-bag')
                    ->iconColor('success')
                    ->actions([
                        Action::make('view_order')
                            ->label('View Order')
                            ->url(route('filament.admin.resources.orders.edit', $order->id))
                            ->button(),
                    ]);

                $data = $notification->getDatabaseMessage();

                foreach ($admins as $admin) {
                    $databaseNotification = new DatabaseNotification($data);
                    $databaseNotification->id = (string) Str::uuid();
                    $admin->notifyNow($databaseNotification);
                }
            }
        } catch (Exception $e) {
            Log::error("Failed to send Admin in-app notification for Order #{$order->order_number}: " . $e->getMessage());
        }

        // 2. Admin Instant SMS Alert (if enabled in Admin SMS Setup)
        try {
            $adminAlertEnabled = (bool) filter_var(Setting::get('sms_trigger_admin_alert', true), FILTER_VALIDATE_BOOLEAN);
            $adminPhone = Setting::get('sms_admin_phone', '');

            if ($adminAlertEnabled && !empty($adminPhone)) {
                $adminSmsMsg = SmsService::renderOrderTemplate(
                    'sms_template_admin_alert',
                    $order,
                    '[New Order Alert] #{order_number} received from {customer_name} ({customer_phone}) for BDT {amount}.'
                );

                $smsLog = SmsLog::create([
                    'order_id'   => $order->id,
                    'user_id'    => null,
                    'recipient'  => $adminPhone,
                    'message'    => $adminSmsMsg,
                    'gateway'    => Setting::get('sms_provider', 'greenweb'),
                    'status'     => 'pending',
                ]);

                SendOrderSmsJob::dispatch($smsLog);
            }
        } catch (Exception $e) {
            Log::error("Failed to send Admin SMS Alert: " . $e->getMessage());
        }
    }

    /**
     * Notify customer on initial order placement (Email & SMS).
     */
    public static function notifyCustomerOrderPlaced(Order $order): void
    {
        // 1. Customer Confirmation Email
        if (!empty($order->customer_email)) {
            try {
                $order->loadMissing('orderItems');
                Mail::to($order->customer_email)->queue(new OrderConfirmationMail($order));
            } catch (Exception $e) {
                Log::error("Failed to send order confirmation email to {$order->customer_email}: " . $e->getMessage());
            }
        }

        // 2. Customer Confirmation SMS (if enabled in Admin SMS Setup)
        if (!empty($order->customer_phone)) {
            try {
                $smsEnabled = (bool) filter_var(Setting::get('sms_trigger_order_placed', true), FILTER_VALIDATE_BOOLEAN);

                if ($smsEnabled) {
                    $smsMessage = SmsService::renderOrderTemplate(
                        'sms_template_order_placed',
                        $order,
                        'Dear {customer_name}, your order #{order_number} of BDT {amount} has been placed successfully! Track: {tracking_url}'
                    );

                    $smsLog = SmsLog::create([
                        'order_id'   => $order->id,
                        'user_id'    => $order->user_id,
                        'recipient'  => $order->customer_phone,
                        'message'    => $smsMessage,
                        'gateway'    => Setting::get('sms_provider', 'greenweb'),
                        'status'     => 'pending',
                    ]);

                    SendOrderSmsJob::dispatch($smsLog);
                }
            } catch (Exception $e) {
                Log::error("Failed to send order confirmation SMS to {$order->customer_phone}: " . $e->getMessage());
            }
        }
    }

    /**
     * Notify customer when order status or courier tracking changes.
     */
    public static function notifyCustomerStatusUpdated(Order $order, string $previousStatus, string $newStatus): void
    {
        // 1. Status Update Email
        if (!empty($order->customer_email)) {
            try {
                $order->loadMissing('orderItems');
                Mail::to($order->customer_email)->queue(new OrderStatusUpdatedMail($order, $previousStatus, $newStatus));
            } catch (Exception $e) {
                Log::error("Failed to send order status email to {$order->customer_email}: " . $e->getMessage());
            }
        }

        // 2. Status Update SMS (if enabled in Admin SMS Setup)
        if (!empty($order->customer_phone)) {
            try {
                $shouldSend = false;
                $templateKey = '';
                $defaultTemplate = '';

                if ($newStatus === 'shipped') {
                    $shouldSend = (bool) filter_var(Setting::get('sms_trigger_order_shipped', true), FILTER_VALIDATE_BOOLEAN);
                    $templateKey = 'sms_template_order_shipped';
                    $defaultTemplate = 'Dear {customer_name}, your order #{order_number} has been dispatched with {courier_name} (Consignment #{consignment_id}). Track: {tracking_url}';
                } elseif ($newStatus === 'delivered') {
                    $shouldSend = (bool) filter_var(Setting::get('sms_trigger_order_delivered', true), FILTER_VALIDATE_BOOLEAN);
                    $templateKey = 'sms_template_order_delivered';
                    $defaultTemplate = 'Dear {customer_name}, your order #{order_number} has been delivered successfully! Thank you for shopping with {store_name}.';
                } elseif ($newStatus === 'cancelled') {
                    $shouldSend = (bool) filter_var(Setting::get('sms_trigger_order_cancelled', true), FILTER_VALIDATE_BOOLEAN);
                    $templateKey = 'sms_template_order_cancelled';
                    $defaultTemplate = 'Dear {customer_name}, your order #{order_number} has been cancelled. If you have any inquiries, please contact customer care.';
                }

                if ($shouldSend && !empty($templateKey)) {
                    $smsMessage = SmsService::renderOrderTemplate($templateKey, $order, $defaultTemplate);

                    $smsLog = SmsLog::create([
                        'order_id'   => $order->id,
                        'user_id'    => $order->user_id,
                        'recipient'  => $order->customer_phone,
                        'message'    => $smsMessage,
                        'gateway'    => Setting::get('sms_provider', 'greenweb'),
                        'status'     => 'pending',
                    ]);

                    SendOrderSmsJob::dispatch($smsLog);
                }
            } catch (Exception $e) {
                Log::error("Failed to send order status SMS to {$order->customer_phone}: " . $e->getMessage());
            }
        }
    }

    /**
     * Send custom SMS to customer.
     */
    public static function sendCustomSms(Order $order, string $message): SmsLog
    {
        $smsLog = SmsLog::create([
            'order_id'   => $order->id,
            'user_id'    => $order->user_id,
            'recipient'  => $order->customer_phone,
            'message'    => $message,
            'gateway'    => Setting::get('sms_provider', 'greenweb'),
            'status'     => 'pending',
        ]);

        SendOrderSmsJob::dispatch($smsLog);

        return $smsLog;
    }
}