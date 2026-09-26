<?php

namespace App\Livewire;

use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\SettingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CartPage extends Component
{
    public string $couponCode = '';

    public function updateQuantity($cartKey, $quantity)
    {
        CartService::updateQuantity($cartKey, $quantity);
        $this->dispatch('cart-updated');
        $this->refreshCoupon();
    }

    public function removeItem($cartKey)
    {
        CartService::remove($cartKey);
        $this->dispatch('cart-updated');
        $this->refreshCoupon();
    }

    public function applyCoupon()
    {
        $this->validate([
            'couponCode' => 'required|string|min:2|max:50',
        ]);

        $subtotal = CartService::getTotal();
        $user = Auth::user();

        $result = DiscountService::apply(
            $this->couponCode,
            $subtotal,
            $user?->id,
            $user?->phone
        );

        if (!$result['valid']) {
            $this->addError('couponCode', $result['message']);
            return;
        }

        session()->put('applied_coupon', [
            'id'       => $result['coupon']->id,
            'code'     => $result['coupon']->code,
            'discount' => $result['discount'],
            'type'     => $result['coupon']->type,
            'value'    => $result['coupon']->value,
        ]);

        $this->couponCode = '';
        session()->flash('coupon_success', $result['message']);
    }

    public function removeCoupon()
    {
        session()->forget('applied_coupon');
        session()->flash('coupon_removed', 'Promo code removed.');
    }

    protected function refreshCoupon()
    {
        if (session()->has('applied_coupon')) {
            $applied = session('applied_coupon');
            $subtotal = CartService::getTotal();
            $user = Auth::user();

            $result = DiscountService::apply(
                $applied['code'],
                $subtotal,
                $user?->id,
                $user?->phone
            );

            if ($result['valid']) {
                session()->put('applied_coupon', [
                    'id'       => $result['coupon']->id,
                    'code'     => $result['coupon']->code,
                    'discount' => $result['discount'],
                    'type'     => $result['coupon']->type,
                    'value'    => $result['coupon']->value,
                ]);
            } else {
                session()->forget('applied_coupon');
            }
        }
    }

    public function render()
    {
        $cart = CartService::getCart();
        $subtotal = CartService::getTotal();

        // Promo Coupon Discount
        $appliedCoupon = session('applied_coupon', null);
        $promoDiscount = $appliedCoupon ? (float) $appliedCoupon['discount'] : 0.0;

        // Automatic Spending Tier Discount (Admin Rule)
        $autoDiscountData = SettingService::calculateAutoSpendingDiscount($subtotal);
        $autoDiscountAmount = $autoDiscountData['active'] ? (float) $autoDiscountData['discount'] : 0.0;

        $totalDiscount = min($subtotal, $promoDiscount + $autoDiscountAmount);

        // Dynamic Shipping Rules from Admin Settings
        $freeShippingThreshold = SettingService::getFreeShippingThreshold();
        $isFreeShippingEnabled = SettingService::isFreeShippingEnabled();
        $estimatedShippingFee = SettingService::calculateShippingFee($subtotal, 'inside_dhaka');
        $payableTotal = max(0, ($subtotal - $totalDiscount) + $estimatedShippingFee);

        return view('livewire.cart-page', [
            'cart'                  => $cart,
            'total'                 => $subtotal,
            'subtotal'              => $subtotal,
            'promoDiscount'         => $promoDiscount,
            'autoDiscountData'      => $autoDiscountData,
            'autoDiscountAmount'    => $autoDiscountAmount,
            'totalDiscount'         => $totalDiscount,
            'appliedCoupon'         => $appliedCoupon,
            'freeShippingThreshold' => $freeShippingThreshold,
            'isFreeShippingEnabled' => $isFreeShippingEnabled,
            'estimatedShippingFee'  => $estimatedShippingFee,
            'payableTotal'          => $payableTotal,
        ]);
    }
}