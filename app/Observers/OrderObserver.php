<?php

namespace App\Observers;

use App\Jobs\SendOrderSmsJob;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\SmsLog;
use App\Services\UrlShortenerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Only trigger when order_status has actually changed
        if (!$order->wasChanged('order_status')) {
            return;
        }

        $oldStatus = strtolower((string) $order->getOriginal('order_status'));
        $newStatus = strtolower((string) $order->order_status);

        // 1. Handle Automatic Inventory Restoration / Re-deduction
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            $this->restoreInventory($order);
        } elseif ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
            $this->deductInventory($order);
        }

        // 2. Reward Loyalty Points
        if ($newStatus === 'delivered' && $oldStatus !== 'delivered') {
            $this->rewardLoyaltyPoints($order);
        }

        // 3. Send SMS Notification
        $this->sendStatusSmsNotification($order, $newStatus);

        // 4. Send Email Notification
        $this->sendStatusEmailNotification($order, $oldStatus, $newStatus);
    }

    /**
     * Reward Loyalty Points for Delivered Order
     */
    protected function rewardLoyaltyPoints(Order $order): void
    {
        if (!\App\Services\SettingService::isLoyaltyEnabled()) return;
        if (!$order->user_id) return; // Only for logged-in users
        
        $user = $order->user;
        if (!$user) return;

        // Check if already rewarded (prevent duplicates on status toggle)
        $exists = \App\Models\LoyaltyTransaction::where('order_id', $order->id)
                    ->where('type', 'earned')
                    ->exists();
        if ($exists) return;

        $multiplier = \App\Services\SettingService::getLoyaltyPointsPer100Spend();
        // Calculate based on grand total
        $pointsToReward = (int) floor(($order->grand_total / 100) * $multiplier);

        if ($pointsToReward > 0) {
            \App\Services\LoyaltyService::addPoints(
                $user,
                $pointsToReward,
                "Reward for Order #{$order->order_number}",
                $order->id
            );
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // If an active (non-cancelled) order is deleted, restore the reserved stock
        if (strtolower((string) $order->order_status) !== 'cancelled') {
            $this->restoreInventory($order);
        }
    }

    /**
     * Restore stock quantities for all items in the cancelled/deleted order.
     */
    protected function restoreInventory(Order $order): void
    {
        try {
            $order->loadMissing('orderItems');

            foreach ($order->orderItems as $item) {
                if ($item->product_id && $item->quantity > 0) {
                    Product::where('id', $item->product_id)->increment('stock_quantity', $item->quantity);
                    Log::info("Restored {$item->quantity} stock for Product ID #{$item->product_id} ('{$item->product_name}') from Order #{$order->order_number}.");
                }
            }
        } catch (Throwable $e) {
            Log::error("Failed to restore inventory for Order #{$order->order_number}: " . $e->getMessage());
        }
    }

    /**
     * Re-deduct stock quantities when an order is reopened from cancelled status.
     */
    protected function deductInventory(Order $order): void
    {
        try {
            $order->loadMissing('orderItems');

            foreach ($order->orderItems as $item) {
                if ($item->product_id && $item->quantity > 0) {
                    Product::where('id', $item->product_id)->decrement('stock_quantity', $item->quantity);
                    Log::info("Re-deducted {$item->quantity} stock for Product ID #{$item->product_id} ('{$item->product_name}') as Order #{$order->order_number} was reopened.");
                }
            }
        } catch (Throwable $e) {
            Log::error("Failed to re-deduct inventory for Order #{$order->order_number}: " . $e->getMessage());
        }
    }

    /**
     * Dispatch SMS notification based on the new order status.
     */
    protected function sendStatusSmsNotification(Order $order, string $newStatus): void
    {
        if (empty($order->customer_phone)) {
            return;
        }

        try {
            if (strtolower($newStatus) === 'shipped') {
                $courierPart = !empty($order->courier_name) ? " via {$order->courier_name}" : "";
                $trackingCodePart = !empty($order->tracking_number) ? " Code: {$order->tracking_number}." : "";
                $destinationUrl = !empty($order->tracking_url) ? $order->tracking_url : route('order.track', $order->order_number);
                $shortUrl = UrlShortenerService::shorten($destinationUrl, $order->id);

                $smsMessage = "Great news {$order->customer_name}! Order #{$order->order_number} shipped{$courierPart}.{$trackingCodePart} Track: {$shortUrl}";
            } else {
                $trackUrl = UrlShortenerService::shorten(route('order.track', $order->order_number), $order->id);

                $smsMessage = match (strtolower($newStatus)) {
                    'processing' => "Dear {$order->customer_name}, your order #{$order->order_number} is now being processed. Track: {$trackUrl}",
                    'delivered'  => "Dear {$order->customer_name}, your order #{$order->order_number} has been successfully delivered. Thank you for choosing us!",
                    'cancelled'  => "Dear {$order->customer_name}, your order #{$order->order_number} has been cancelled. Please contact support for any questions.",
                    default      => "Dear {$order->customer_name}, the status of order #{$order->order_number} is now " . ucfirst(str_replace('_', ' ', $newStatus)) . ". Track: {$trackUrl}",
                };
            }

            $smsLog = SmsLog::create([
                'order_id'   => $order->id,
                'user_id'    => $order->user_id,
                'recipient'  => $order->customer_phone,
                'message'    => $smsMessage,
                'gateway'    => config('services.sms_default_gateway', 'local_sms'),
                'status'     => 'pending',
            ]);

            SendOrderSmsJob::dispatch($smsLog);

        } catch (Throwable $e) {
            Log::error("Failed to queue status SMS for Order #{$order->order_number}: " . $e->getMessage());
        }
    }

    /**
     * Dispatch email notification based on the updated order status.
     */
    protected function sendStatusEmailNotification(Order $order, string $oldStatus, string $newStatus): void
    {
        $recipientEmail = $order->getEmailRecipient();

        if (empty($recipientEmail)) {
            return;
        }

        try {
            $order->loadMissing('orderItems');
            Mail::to($recipientEmail)->queue(new OrderStatusUpdatedMail($order, $oldStatus, $newStatus));
        } catch (Throwable $e) {
            Log::error("Failed to queue status email for Order #{$order->order_number}: " . $e->getMessage());
        }
    }
}
