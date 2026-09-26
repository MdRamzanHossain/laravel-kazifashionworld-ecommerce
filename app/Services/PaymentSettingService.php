<?php

namespace App\Services;

use App\Models\Setting;

class PaymentSettingService
{
    /**
     * bKash Payment Gateway Settings
     */
    public static function isBkashEnabled(): bool
    {
        return (bool) filter_var(Setting::get('payment_bkash_enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    public static function getBkashConfig(): array
    {
        return [
            'enabled'       => self::isBkashEnabled(),
            'sandbox_mode'  => (bool) filter_var(Setting::get('payment_bkash_sandbox', true), FILTER_VALIDATE_BOOLEAN),
            'app_key'       => Setting::get('payment_bkash_app_key', config('services.bkash.app_key', '')),
            'app_secret'    => Setting::get('payment_bkash_app_secret', config('services.bkash.app_secret', '')),
            'username'      => Setting::get('payment_bkash_username', config('services.bkash.username', '')),
            'password'      => Setting::get('payment_bkash_password', config('services.bkash.password', '')),
            'wallet_number' => Setting::get('payment_bkash_wallet_number', '01735940279'),
            'type'          => Setting::get('payment_bkash_type', 'tokenized'), // tokenized or manual
            'instructions'  => Setting::get('payment_bkash_instructions', 'Pay seamlessly using your bKash digital wallet with instant auto-confirmation.'),
        ];
    }

    /**
     * Nagad Payment Gateway Settings
     */
    public static function isNagadEnabled(): bool
    {
        return (bool) filter_var(Setting::get('payment_nagad_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function getNagadConfig(): array
    {
        return [
            'enabled'       => self::isNagadEnabled(),
            'sandbox_mode'  => (bool) filter_var(Setting::get('payment_nagad_sandbox', true), FILTER_VALIDATE_BOOLEAN),
            'merchant_id'   => Setting::get('payment_nagad_merchant_id', ''),
            'public_key'    => Setting::get('payment_nagad_public_key', ''),
            'private_key'   => Setting::get('payment_nagad_private_key', ''),
            'wallet_number' => Setting::get('payment_nagad_wallet_number', '01735940279'),
            'instructions'  => Setting::get('payment_nagad_instructions', 'Pay via Nagad wallet or mobile app.'),
        ];
    }

    /**
     * SSLCommerz Payment Gateway Settings (Cards, Internet Banking, Mobile Banking)
     */
    public static function isSslCommerzEnabled(): bool
    {
        return (bool) filter_var(Setting::get('payment_sslcommerz_enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    public static function getSslCommerzConfig(): array
    {
        return [
            'enabled'        => self::isSslCommerzEnabled(),
            'sandbox_mode'   => (bool) filter_var(Setting::get('payment_sslcommerz_sandbox', true), FILTER_VALIDATE_BOOLEAN),
            'store_id'       => Setting::get('payment_sslcommerz_store_id', config('services.sslcommerz.store_id', 'testbox')),
            'store_password' => Setting::get('payment_sslcommerz_store_password', config('services.sslcommerz.store_password', 'qwerty')),
            'instructions'   => Setting::get('payment_sslcommerz_instructions', 'Pay securely with Visa, Mastercard, AMEX, Internet Banking, or other MFS wallets.'),
        ];
    }

    /**
     * Cash on Delivery (COD) Settings
     */
    public static function isCodEnabled(): bool
    {
        return (bool) filter_var(Setting::get('payment_cod_enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    public static function getCodConfig(): array
    {
        return [
            'enabled'      => self::isCodEnabled(),
            'extra_fee'    => (float) Setting::get('payment_cod_extra_fee', 0.00),
            'instructions' => Setting::get('payment_cod_instructions', 'Pay with cash upon parcel delivery to your doorstep. Please keep exact change ready.'),
        ];
    }

    /**
     * Manual Bank Transfer / Rocket Settings
     */
    public static function isBankTransferEnabled(): bool
    {
        return (bool) filter_var(Setting::get('payment_bank_enabled', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function getBankTransferConfig(): array
    {
        return [
            'enabled'        => self::isBankTransferEnabled(),
            'bank_name'      => Setting::get('payment_bank_name', 'City Bank / BRAC Bank'),
            'account_name'   => Setting::get('payment_bank_account_name', 'Kazi Fashion World Ltd.'),
            'account_number' => Setting::get('payment_bank_account_number', '1234567890'),
            'branch_name'    => Setting::get('payment_bank_branch', 'Gulshan Branch, Dhaka'),
            'routing_number' => Setting::get('payment_bank_routing', '225271234'),
            'instructions'   => Setting::get('payment_bank_instructions', 'Transfer amount to our official corporate bank account and attach/SMS transaction proof.'),
        ];
    }
}