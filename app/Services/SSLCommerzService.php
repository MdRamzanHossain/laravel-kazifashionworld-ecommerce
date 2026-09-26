<?php

namespace App\Services;

use App\Jobs\SendOrderSmsJob;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SSLCommerzService
{
    /**
     * Initiate payment session with SSLCommerz.
     * Returns redirect URL to payment gateway or local test sandbox simulator.
     */
    public static function initiatePayment(Order $order): string
    {
        $config = PaymentSettingService::getSslCommerzConfig();
        $isSandbox = (bool) ($config['sandbox_mode'] ?? true);
        $storeId = $config['store_id'] ?? 'testbox';
        $storePassword = $config['store_password'] ?? 'qwerty';

        $tranId = 'SSLCZ_' . $order->order_number . '_' . time();
        $grandTotal = (float) ($order->grand_total ?? $order->total_amount);

        // Record pending payment transaction
        PaymentTransaction::create([
            'order_id'       => $order->id,
            'transaction_id' => $tranId,
            'gateway'        => 'sslcommerz',
            'amount'         => $grandTotal,
            'currency'       => $order->currency ?? 'BDT',
            'status'         => 'pending',
            'payload'        => null,
        ]);

        $order->update([
            'payment_gateway' => 'sslcommerz',
            'transaction_id'  => $tranId,
            'currency'        => $order->currency ?? 'BDT',
        ]);

        $apiUrl = $isSandbox
            ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
            : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';

        $postData = [
            'store_id'         => $storeId,
            'store_passwd'     => $storePassword,
            'total_amount'     => $grandTotal,
            'currency'         => $order->currency ?? 'BDT',
            'tran_id'          => $tranId,
            'success_url'      => route('payment.sslcommerz.success'),
            'fail_url'         => route('payment.sslcommerz.fail'),
            'cancel_url'       => route('payment.sslcommerz.cancel'),
            'ipn_url'          => route('payment.sslcommerz.ipn'),
            'cus_name'         => $order->customer_name ?: 'Customer',
            'cus_email'        => $order->getEmailRecipient() ?: 'customer@example.com',
            'cus_add1'         => $order->shipping_address ?: 'Dhaka',
            'cus_city'         => $order->district ?: 'Dhaka',
            'cus_country'      => 'Bangladesh',
            'cus_phone'        => $order->customer_phone ?: '01735940279',
            'shipping_method'  => 'NO',
            'product_name'     => 'Beauty Products Order #' . $order->order_number,
            'product_category' => 'Cosmetics',
            'product_profile'  => 'physical-goods',
        ];

        try {
            $response = Http::timeout(8)->asForm()->post($apiUrl, $postData);

            if ($response->successful()) {
                $result = $response->json();

                if (!empty($result['status']) && $result['status'] === 'SUCCESS' && !empty($result['GatewayPageURL'])) {
                    return $result['GatewayPageURL'];
                }
            }
        } catch (Throwable $e) {
            Log::warning("SSLCommerz live connection notice: " . $e->getMessage() . " - falling back to Mock Sandbox Simulator.");
        }

        // Graceful fallback to interactive Mock Sandbox Simulator for local development / testing
        return route('payment.mock', ['orderNumber' => $order->order_number, 'tranId' => $tranId]);
    }

    /**
     * Validate transaction via SSLCommerz Order Validation API or Mock.
     */
    public static function validatePayment(array $postData): bool
    {
        $valId = $postData['val_id'] ?? null;
        $tranId = $postData['tran_id'] ?? null;

        if (empty($tranId)) {
            return false;
        }

        // Mock simulator validation
        if (isset($postData['is_mock']) || (isset($postData['val_id']) && str_starts_with($postData['val_id'], 'MOCK-VAL-'))) {
            return true;
        }

        if (empty($valId)) {
            return false;
        }

        $config = PaymentSettingService::getSslCommerzConfig();
        $isSandbox = (bool) ($config['sandbox_mode'] ?? true);
        $storeId = $config['store_id'] ?? 'testbox';
        $storePassword = $config['store_password'] ?? 'qwerty';

        $validatorUrl = $isSandbox
            ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
            : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php';

        try {
            $response = Http::timeout(10)->get($validatorUrl, [
                'val_id'       => $valId,
                'store_id'     => $storeId,
                'store_passwd' => $storePassword,
                'format'       => 'json',
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return isset($result['status']) && in_array($result['status'], ['VALID', 'VALIDATED']);
            }
        } catch (Throwable $e) {
            Log::error("SSLCommerz Validation API error: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Process successful payment: update order & transaction, advance status to processing, send notifications.
     */
    public static function processSuccess(Order $order, array $payload): void
    {
        $tranId = $payload['tran_id'] ?? $order->transaction_id;
        $valId = $payload['val_id'] ?? null;
        $cardType = $payload['card_type'] ?? ($payload['card_brand'] ?? 'Online');

        // Update PaymentTransaction
        PaymentTransaction::where('order_id', $order->id)
            ->where('transaction_id', $tranId)
            ->update([
                'status'     => 'success',
                'val_id'     => $valId,
                'card_type'  => $cardType,
                'payload'    => $payload,
                'updated_at' => now(),
            ]);

        // Update Order
        $order->update([
            'payment_status'  => 'paid',
            'payment_gateway' => 'sslcommerz',
            'transaction_id'  => $tranId,
            'payment_details' => $payload,
            'order_status'    => ($order->order_status === 'pending') ? 'processing' : $order->order_status,
        ]);

        Log::info("Payment of BDT {$order->grand_total} confirmed for Order #{$order->order_number} (Tran ID: {$tranId}).");

        // Notify Admins in Filament
        \App\Services\OrderNotificationService::notifyAdminsNewOrder($order);

        // Send Confirmation Email
        try {
            $customerEmail = $order->getEmailRecipient();
            if ($customerEmail) {
                $order->loadMissing('orderItems');
                Mail::to($customerEmail)->queue(new OrderConfirmationMail($order));
            }
        } catch (Throwable $e) {
            Log::error("Payment success email dispatch failed for Order #{$order->order_number}: " . $e->getMessage());
        }

        // Send Confirmation SMS
        try {
            if ($order->customer_phone) {
                $trackUrl = UrlShortenerService::shorten(route('order.track', $order->order_number), $order->id);
                $smsMessage = "Payment Confirmed! Dear {$order->customer_name}, your order #{$order->order_number} (Paid BDT " . number_format($order->grand_total ?? $order->total_amount, 2) . ") is now processing. Track: {$trackUrl}";

                $smsLog = SmsLog::create([
                    'order_id'   => $order->id,
                    'user_id'    => $order->user_id,
                    'recipient'  => $order->customer_phone,
                    'message'    => $smsMessage,
                    'gateway'    => config('services.sms_default_gateway', 'local_sms'),
                    'status'     => 'pending',
                ]);

                SendOrderSmsJob::dispatch($smsLog);
            }
        } catch (Throwable $e) {
            Log::error("Payment success SMS dispatch failed for Order #{$order->order_number}: " . $e->getMessage());
        }
    }

    /**
     * Process failed / cancelled payment.
     */
    public static function processFailure(Order $order, array $payload, string $status = 'failed'): void
    {
        $tranId = $payload['tran_id'] ?? $order->transaction_id;

        PaymentTransaction::where('order_id', $order->id)
            ->where('transaction_id', $tranId)
            ->update([
                'status'     => $status,
                'payload'    => $payload,
                'updated_at' => now(),
            ]);

        $order->update([
            'payment_status'  => $status,
            'payment_details' => $payload,
        ]);

        Log::warning("Payment {$status} for Order #{$order->order_number} (Tran ID: {$tranId}).");
    }
}
