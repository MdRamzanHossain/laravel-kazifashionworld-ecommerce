<?php

namespace App\Services;

use App\Jobs\SendOrderSmsJob;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BkashService
{
    /**
     * Get bKash Tokenized API Base URL.
     */
    public static function getBaseUrl(): string
    {
        $config = PaymentSettingService::getBkashConfig();
        $isSandbox = $config['sandbox_mode'] ?? true;
        return $isSandbox
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta';
    }

    /**
     * Grant or retrieve cached bKash ID Token.
     */
    public static function grantToken(): ?string
    {
        $cachedToken = Cache::get('bkash_id_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        $config = PaymentSettingService::getBkashConfig();
        $appKey = $config['app_key'] ?? '';
        $appSecret = $config['app_secret'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';

        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'username'     => $username,
                    'password'     => $password,
                ])
                ->post(self::getBaseUrl() . '/tokenized/checkout/token/grant', [
                    'app_key'    => $appKey,
                    'app_secret' => $appSecret,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['id_token'])) {
                    $expiresIn = (int) ($data['expires_in'] ?? 3600);
                    Cache::put('bkash_id_token', $data['id_token'], now()->addSeconds(max(60, $expiresIn - 120)));
                    return $data['id_token'];
                }
            }
        } catch (Throwable $e) {
            Log::warning("bKash grant token notice: " . $e->getMessage() . " - falling back to Mock Sandbox Simulator.");
        }

        return null;
    }

    /**
     * Create payment request with bKash Tokenized Checkout API.
     */
    public static function createPayment(Order $order): string
    {
        $config = PaymentSettingService::getBkashConfig();
        $appKey = $config['app_key'] ?? '';
        $grandTotal = (float) ($order->grand_total ?? $order->total_amount);
        $mockPaymentId = 'MOCK-BKASH-' . $order->order_number . '-' . time();

        // Record pending payment transaction
        PaymentTransaction::create([
            'order_id'       => $order->id,
            'transaction_id' => $mockPaymentId,
            'gateway'        => 'bkash',
            'amount'         => $grandTotal,
            'currency'       => $order->currency ?? 'BDT',
            'status'         => 'pending',
            'payload'        => null,
        ]);

        $order->update([
            'payment_gateway' => 'bkash',
            'transaction_id'  => $mockPaymentId,
            'currency'        => $order->currency ?? 'BDT',
        ]);

        $idToken = self::grantToken();

        if ($idToken) {
            try {
                $response = Http::timeout(8)
                    ->withHeaders([
                        'Content-Type'  => 'application/json',
                        'Authorization' => $idToken,
                        'X-APP-Key'     => $appKey,
                    ])
                    ->post(self::getBaseUrl() . '/tokenized/checkout/create', [
                        'mode'                  => '0011',
                        'payerReference'        => $order->customer_phone ?: '01735940279',
                        'callbackURL'           => route('payment.bkash.callback'),
                        'amount'                => number_format($grandTotal, 2, '.', ''),
                        'currency'              => $order->currency ?? 'BDT',
                        'intent'                => 'sale',
                        'merchantInvoiceNumber' => $order->order_number,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['bkashURL']) && ($data['statusCode'] ?? '') === '0000') {
                        $order->update(['transaction_id' => $data['paymentID']]);
                        return $data['bkashURL'];
                    }
                }
            } catch (Throwable $e) {
                Log::warning("bKash create payment notice: " . $e->getMessage() . " - falling back to Mock Simulator.");
            }
        }

        // Graceful fallback to interactive Mock Sandbox Simulator for local development / testing
        return route('payment.bkash.mock', [
            'orderNumber' => $order->order_number,
            'paymentID'   => $mockPaymentId,
        ]);
    }

    /**
     * Execute payment request with bKash Tokenized Checkout API.
     */
    public static function executePayment(string $paymentId): array
    {
        // Mock simulator bypass
        if (str_starts_with($paymentId, 'MOCK-BKASH-') || request()->filled('is_mock')) {
            $trxId = 'BKASH_TRX_' . strtoupper(substr(md5(uniqid()), 0, 10));
            return [
                'statusCode'     => '0000',
                'statusMessage'  => 'Successful',
                'paymentID'      => $paymentId,
                'trxID'          => $trxId,
                'amount'         => request('amount', '0.00'),
                'customerMsisdn' => request('customerMsisdn', '017XXXXXXXX'),
                'currency'       => 'BDT',
                'intent'         => 'sale',
                'paymentExecuteTime' => now()->format('Y-m-d H:i:s'),
            ];
        }

        $idToken = self::grantToken();
        $appKey = config('services.bkash.app_key', '');

        if (!$idToken) {
            return ['statusCode' => '9999', 'statusMessage' => 'Failed to obtain bKash token'];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type'  => 'application/json',
                    'Authorization' => $idToken,
                    'X-APP-Key'     => $appKey,
                ])
                ->post(self::getBaseUrl() . '/tokenized/checkout/execute', [
                    'paymentID' => $paymentId,
                ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (Throwable $e) {
            Log::error("bKash execute payment error: " . $e->getMessage());
        }

        return ['statusCode' => '9999', 'statusMessage' => 'bKash API execution failed'];
    }

    /**
     * Process successful bKash payment: update order & transaction, advance status to processing, send notifications.
     */
    public static function processSuccess(Order $order, array $payload): void
    {
        $trxId = $payload['trxID'] ?? ('BKASH_' . time());
        $paymentId = $payload['paymentID'] ?? $order->transaction_id;

        // Update PaymentTransaction
        PaymentTransaction::where('order_id', $order->id)
            ->where(function ($query) use ($paymentId, $trxId) {
                $query->where('transaction_id', $paymentId)
                      ->orWhere('transaction_id', $trxId);
            })
            ->update([
                'status'         => 'success',
                'transaction_id' => $trxId,
                'val_id'         => $paymentId,
                'card_type'      => 'bKash-Tokenized',
                'card_brand'     => 'bKash',
                'payload'        => $payload,
                'updated_at'     => now(),
            ]);

        // Update Order
        $order->update([
            'payment_status'  => 'paid',
            'payment_gateway' => 'bkash',
            'transaction_id'  => $trxId,
            'payment_details' => $payload,
            'order_status'    => ($order->order_status === 'pending') ? 'processing' : $order->order_status,
        ]);

        Log::info("bKash payment of BDT {$order->grand_total} confirmed for Order #{$order->order_number} (TrxID: {$trxId}).");

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
            Log::error("bKash success email dispatch failed for Order #{$order->order_number}: " . $e->getMessage());
        }

        // Send Confirmation SMS
        try {
            if ($order->customer_phone) {
                $trackUrl = UrlShortenerService::shorten(route('order.track', $order->order_number), $order->id);
                $smsMessage = "bKash Payment Received! Dear {$order->customer_name}, order #{$order->order_number} (Paid BDT " . number_format($order->grand_total ?? $order->total_amount, 2) . ", TrxID: {$trxId}) is now processing. Track: {$trackUrl}";

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
            Log::error("bKash success SMS dispatch failed for Order #{$order->order_number}: " . $e->getMessage());
        }
    }

    /**
     * Process failed / cancelled bKash payment.
     */
    public static function processFailure(Order $order, array $payload, string $status = 'failed'): void
    {
        $paymentId = $payload['paymentID'] ?? $order->transaction_id;

        PaymentTransaction::where('order_id', $order->id)
            ->where('transaction_id', $paymentId)
            ->update([
                'status'     => $status,
                'payload'    => $payload,
                'updated_at' => now(),
            ]);

        $order->update([
            'payment_status'  => $status,
            'payment_details' => $payload,
        ]);

        Log::warning("bKash payment {$status} for Order #{$order->order_number} (PaymentID: {$paymentId}).");
    }
}
