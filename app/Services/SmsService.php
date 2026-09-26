<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class SmsService
{
    /**
     * Get active SMS Gateway configuration from Admin Settings
     */
    public static function getConfig(): array
    {
        $provider = Setting::get('sms_provider', 'greenweb');

        return [
            'provider'       => $provider,
            'api_key'        => Setting::get('sms_api_key', config('services.local_sms.api_key', '')),
            'sender_id'      => Setting::get('sms_sender_id', ''),
            'client_id'      => Setting::get('sms_client_id', ''),
            'custom_url'     => Setting::get('sms_custom_url', config('services.local_sms.url', '')),
            'custom_method'  => Setting::get('sms_custom_method', 'GET'),
            'admin_phone'    => Setting::get('sms_admin_phone', '01735940279'),
            'admin_alert'    => (bool) filter_var(Setting::get('sms_trigger_admin_alert', true), FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Dispatch and send the SMS represented by the SmsLog record.
     *
     * @throws Exception
     */
    public static function send(SmsLog $smsLog): array
    {
        $config = self::getConfig();
        $provider = $smsLog->gateway ?: $config['provider'];
        $recipient = self::formatBangladeshPhone($smsLog->recipient);
        $message = $smsLog->message;

        try {
            $response = match ($provider) {
                'greenweb'   => self::sendViaGreenweb($recipient, $message, $config),
                'bulksmsbd'  => self::sendViaBulkSmsBd($recipient, $message, $config),
                'mim_sms'    => self::sendViaMimSms($recipient, $message, $config),
                'elitbuzz'   => self::sendViaElitBuzz($recipient, $message, $config),
                'twilio'     => self::sendViaTwilio($recipient, $message),
                default      => self::sendViaCustomGateway($recipient, $message, $config),
            };

            $smsLog->update([
                'status'        => 'sent',
                'error_message' => null,
                'response_data' => $response,
                'sent_at'       => now(),
            ]);

            return $response;
        } catch (Throwable $e) {
            $smsLog->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'sent_at'       => now(),
            ]);

            Log::error("SMS Dispatch Failed [Provider: {$provider}, To: {$recipient}]: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send direct live test SMS (used in Admin Panel Test Tool)
     */
    public static function sendTestSms(string $to, string $message): array
    {
        $config = self::getConfig();
        $provider = $config['provider'];
        $recipient = self::formatBangladeshPhone($to);

        return match ($provider) {
            'greenweb'   => self::sendViaGreenweb($recipient, $message, $config),
            'bulksmsbd'  => self::sendViaBulkSmsBd($recipient, $message, $config),
            'mim_sms'    => self::sendViaMimSms($recipient, $message, $config),
            'elitbuzz'   => self::sendViaElitBuzz($recipient, $message, $config),
            'twilio'     => self::sendViaTwilio($recipient, $message),
            default      => self::sendViaCustomGateway($recipient, $message, $config),
        };
    }

    /**
     * Greenweb SMS Bangladesh API
     */
    protected static function sendViaGreenweb(string $to, string $message, array $config): array
    {
        $token = $config['api_key'];
        if (empty($token)) {
            throw new Exception("Greenweb SMS API Token is missing in Admin SMS Setup.");
        }

        $url = 'https://api.greenweb.com.bd/api.php';
        $response = Http::timeout(12)->get($url, [
            'token' => $token,
            'to'    => $to,
            'msg'   => $message,
        ]);

        if ($response->failed()) {
            throw new Exception("Greenweb SMS API error (" . $response->status() . "): " . $response->body());
        }

        return ['provider' => 'greenweb', 'body' => $response->body(), 'status' => $response->status()];
    }

    /**
     * BulkSMSBD API
     */
    protected static function sendViaBulkSmsBd(string $to, string $message, array $config): array
    {
        $apiKey = $config['api_key'];
        $senderId = $config['sender_id'];

        if (empty($apiKey)) {
            throw new Exception("BulkSMSBD API Key is missing in Admin SMS Setup.");
        }

        $url = 'https://bulksmsbd.net/api/smsapi';
        $response = Http::timeout(12)->get($url, [
            'api_key'  => $apiKey,
            'type'     => 'text',
            'number'   => $to,
            'senderid' => $senderId,
            'message'  => $message,
        ]);

        if ($response->failed()) {
            throw new Exception("BulkSMSBD API error (" . $response->status() . "): " . $response->body());
        }

        return ['provider' => 'bulksmsbd', 'body' => $response->body(), 'status' => $response->status()];
    }

    /**
     * MiM SMS API
     */
    protected static function sendViaMimSms(string $to, string $message, array $config): array
    {
        $token = $config['api_key'];
        $sender = $config['sender_id'];

        if (empty($token)) {
            throw new Exception("MiM SMS API Token is missing in Admin SMS Setup.");
        }

        $url = 'https://api.mimsms.com/api/v3/sms/send';
        $response = Http::timeout(12)->get($url, [
            'token'   => $token,
            'to'      => $to,
            'sender'  => $sender,
            'message' => $message,
        ]);

        if ($response->failed()) {
            throw new Exception("MiM SMS API error (" . $response->status() . "): " . $response->body());
        }

        return ['provider' => 'mim_sms', 'body' => $response->body(), 'status' => $response->status()];
    }

    /**
     * ElitBuzz SMS API
     */
    protected static function sendViaElitBuzz(string $to, string $message, array $config): array
    {
        $apiKey = $config['api_key'];
        $senderId = $config['sender_id'];

        if (empty($apiKey)) {
            throw new Exception("ElitBuzz API Key is missing in Admin SMS Setup.");
        }

        $url = 'https://msg.elitbuzz-bd.com/smsapi';
        $response = Http::timeout(12)->get($url, [
            'api_key'  => $apiKey,
            'type'     => 'text',
            'contacts' => $to,
            'senderid' => $senderId,
            'msg'      => $message,
        ]);

        if ($response->failed()) {
            throw new Exception("ElitBuzz API error (" . $response->status() . "): " . $response->body());
        }

        return ['provider' => 'elitbuzz', 'body' => $response->body(), 'status' => $response->status()];
    }

    /**
     * Twilio Global API
     */
    protected static function sendViaTwilio(string $to, string $message): array
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (empty($sid) || empty($token) || empty($from)) {
            throw new Exception('Twilio credentials are not configured in services.php / .env');
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To'   => $to,
                'Body' => $message,
            ]);

        if ($response->failed()) {
            throw new Exception('Twilio SMS Failed (' . $response->status() . '): ' . $response->body());
        }

        return $response->json() ?? ['provider' => 'twilio', 'status' => 'sent', 'raw' => $response->body()];
    }

    /**
     * Custom HTTP Gateway
     */
    protected static function sendViaCustomGateway(string $to, string $message, array $config): array
    {
        $url = $config['custom_url'] ?: config('services.local_sms.url');
        $apiKey = $config['api_key'] ?: config('services.local_sms.api_key');

        if (empty($url)) {
            throw new Exception('Custom SMS Gateway URL is not configured in Admin Setup.');
        }

        $method = strtoupper($config['custom_method'] ?? 'GET');
        $params = [
            'token'   => $apiKey,
            'api_key' => $apiKey,
            'to'      => $to,
            'number'  => $to,
            'message' => $message,
            'msg'     => $message,
        ];

        if (!empty($config['sender_id'])) {
            $params['senderid'] = $config['sender_id'];
            $params['sender'] = $config['sender_id'];
        }

        $response = ($method === 'POST')
            ? Http::timeout(12)->asForm()->post($url, $params)
            : Http::timeout(12)->get($url, $params);

        if ($response->failed()) {
            throw new Exception('Custom SMS Gateway Failed (' . $response->status() . '): ' . $response->body());
        }

        return ['provider' => 'custom', 'body' => $response->body(), 'status' => $response->status()];
    }

    /**
     * Standardize phone number for Bangladesh gateways (e.g. 017XXXXXXXX or +88017XXXXXXXX -> 88017XXXXXXXX)
     */
    public static function formatBangladeshPhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleaned, '880') && strlen($cleaned) === 13) {
            return $cleaned;
        }

        if (str_starts_with($cleaned, '01') && strlen($cleaned) === 11) {
            return '88' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Render dynamic template variables for an order
     */
    public static function renderOrderTemplate(string $templateKey, Order $order, string $defaultTemplate): string
    {
        $template = Setting::get($templateKey, $defaultTemplate);
        $storeName = Setting::get('theme_store_name', 'Kazi Fashion World');
        $trackUrl = UrlShortenerService::shorten(route('order.track', $order->order_number), $order->id);

        $replacements = [
            '{customer_name}'  => $order->customer_name ?: 'Customer',
            '{customer_phone}' => $order->customer_phone ?: '',
            '{order_number}'   => $order->order_number,
            '{amount}'         => number_format($order->grand_total ?? $order->total_amount, 2),
            '{tracking_url}'   => $trackUrl,
            '{courier_name}'   => $order->courier_name ?: 'Express Courier',
            '{consignment_id}' => $order->consignment_id ?: 'N/A',
            '{store_name}'     => $storeName,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}