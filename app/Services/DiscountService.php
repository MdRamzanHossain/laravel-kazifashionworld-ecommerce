<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DiscountService
{
    /**
     * Validate and calculate discount for a given promo code.
     */
    public static function apply(string $code, float $subtotal, ?int $userId = null, ?string $phone = null): array
    {
        $code = strtoupper(trim($code));

        if (empty($code)) {
            return [
                'valid'    => false,
                'message'  => 'Please enter a promo code.',
                'discount' => 0,
                'coupon'   => null,
            ];
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return [
                'valid'    => false,
                'message'  => "Promo code '{$code}' is invalid or does not exist.",
                'discount' => 0,
                'coupon'   => null,
            ];
        }

        $validation = $coupon->validateCoupon($subtotal, $userId, $phone);

        if (!$validation['valid']) {
            return [
                'valid'    => false,
                'message'  => $validation['message'],
                'discount' => 0,
                'coupon'   => $coupon,
            ];
        }

        return [
            'valid'    => true,
            'message'  => $validation['message'],
            'discount' => $validation['discount'],
            'coupon'   => $coupon,
        ];
    }

    /**
     * Record coupon usage when an order is placed.
     */
    public static function recordUsage(Coupon $coupon, Order $order, float $discountAmount, ?int $userId = null, ?string $phone = null): CouponUsage
    {
        return DB::transaction(function () use ($coupon, $order, $discountAmount, $userId, $phone) {
            // 1. Create audit usage log
            $usage = CouponUsage::create([
                'coupon_id'       => $coupon->id,
                'order_id'        => $order->id,
                'user_id'         => $userId ?? $order->user_id,
                'customer_phone'  => $phone ?? $order->customer_phone,
                'discount_amount' => $discountAmount,
            ]);

            // 2. Increment coupon used counter
            $coupon->increment('used_count');

            return $usage;
        });
    }
}
