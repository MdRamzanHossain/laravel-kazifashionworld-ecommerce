<?php

namespace App\Livewire;

use App\Jobs\SendOrderSmsJob;
use App\Mail\OrderConfirmationMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SmsLog;
use App\Services\BkashService;
use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\SSLCommerzService;
use App\Services\SettingService;
use App\Services\UrlShortenerService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;

class CheckoutPage extends Component
{
    public $name;
    public $email;
    public $phone;
    public $address;
    public $city = 'inside_dhaka';
    public $shippingFee = 80;
    public $paymentMethod = 'cod'; // 'cod', 'bkash', 'online'
    public string $couponCode = '';
    public bool $useLoyaltyPoints = false;

    protected $rules = [
        'name'          => 'required|string|min:3|max:255',
        'email'         => 'required|email|max:255',
        'phone'         => 'required|string|min:11',
        'address'       => 'required|string|min:10',
        'city'          => 'required|in:inside_dhaka,outside_dhaka',
        'paymentMethod' => 'required|in:cod,bkash,online',
    ];

    public function mount()
    {
        // Auto-fill user details if logged in
        if (auth()->check()) {
            $user = auth()->user();
            $this->name    = $user->name ?? $this->name;
            $this->email   = $user->email ?? $this->email;
            $this->phone   = $user->phone ?? $this->phone;
            $this->address = $user->shipping_address ?? $this->address;
            $this->city    = $user->city ?? $this->city;
        }

        $subtotal = CartService::getTotal();
        $this->shippingFee = SettingService::calculateShippingFee($subtotal, $this->city);
    }

    public function updatedCity($value)
    {
        $subtotal = CartService::getTotal();
        $this->shippingFee = SettingService::calculateShippingFee($subtotal, $value);
    }

    public function applyCoupon()
    {
        $this->validate([
            'couponCode' => 'required|string|min:2|max:50',
        ]);

        $subtotal = CartService::getTotal();
        $user = auth()->user();

        $result = DiscountService::apply(
            $this->couponCode,
            $subtotal,
            $user?->id,
            $this->phone ?: $user?->phone
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

    public function placeOrder()
    {
        // 1. Run Form Validation Rules
        $this->validate();

        $cart = CartService::getCart();

        if (empty($cart)) {
            session()->flash('error', 'Your cart is empty. Please add products before checking out.');
            return redirect()->route('cart');
        }

        // 2. Database Transaction with Concurrency-Safe Inventory Locking
        try {
            $order = DB::transaction(function () use ($cart) {
                $subtotal = 0;

                // Validate and lock product stock for all items
                $validatedItems = [];
                foreach ($cart as $item) {
                    $product = Product::where('id', $item['id'])->lockForUpdate()->first();

                    if (!$product) {
                        throw new Exception("Product '{$item['name']}' is no longer available.");
                    }

                    if (($product->stock_quantity ?? 0) < $item['quantity']) {
                        throw new Exception("Sorry, '{$product->name}' only has " . ($product->stock_quantity ?? 0) . " items remaining in stock.");
                    }

                    $unitPrice = (float) ($product->current_price ?? $item['price']);
                    $itemTotal = $unitPrice * $item['quantity'];
                    $subtotal += $itemTotal;

                    $validatedItems[] = [
                        'product'    => $product,
                        'unitPrice'  => $unitPrice,
                        'itemTotal'  => $itemTotal,
                        'quantity'   => $item['quantity'],
                    ];
                }

                // 1. Calculate Promo Coupon Discount
                $promoDiscount = 0.0;
                $couponRecord = null;
                $couponCode = null;

                if (session()->has('applied_coupon')) {
                    $applied = session('applied_coupon');
                    $couponRecord = Coupon::find($applied['id']);

                    if ($couponRecord) {
                        $validation = $couponRecord->validateCoupon($subtotal, auth()->id(), $this->phone);
                        if ($validation['valid']) {
                            $promoDiscount = (float) $validation['discount'];
                            $couponCode = $couponRecord->code;
                        }
                    }
                }

                // 2. Calculate Automatic Spending Tier Discount (if configured in Admin)
                $autoDiscountData = SettingService::calculateAutoSpendingDiscount($subtotal);
                $autoDiscountAmount = $autoDiscountData['active'] ? (float) $autoDiscountData['discount'] : 0.0;

                $totalDiscount = min($subtotal, $promoDiscount + $autoDiscountAmount);

                // 3. Calculate Real-Time Delivery Fee based on Admin threshold rules
                $shippingFee = SettingService::calculateShippingFee($subtotal, $this->city);
                
                // Calculate Loyalty Discount (if toggled)
                $loyaltyDiscount = 0.0;
                $loyaltyPointsUsed = 0;
                if ($this->useLoyaltyPoints && auth()->check() && SettingService::isLoyaltyEnabled()) {
                    $userPoints = auth()->user()->points_balance;
                    $pointValue = SettingService::getLoyaltyRedemptionValue();
                    $maxPointsNeeded = (int) ceil(($subtotal - $totalDiscount + $shippingFee) / $pointValue);
                    
                    $loyaltyPointsUsed = min($userPoints, $maxPointsNeeded);
                    $loyaltyDiscount = $loyaltyPointsUsed * $pointValue;
                }

                $grandTotal = max(0, ($subtotal - $totalDiscount - $loyaltyDiscount) + $shippingFee);

                $createdOrder = Order::create([
                    'order_number'     => 'ORD-' . strtoupper(uniqid()),
                    'user_id'          => auth()->id(),
                    'customer_name'    => $this->name,
                    'customer_email'   => $this->email,
                    'customer_phone'   => $this->phone,
                    'shipping_address' => $this->address . ' (' . ucfirst(str_replace('_', ' ', $this->city)) . ')',
                    'subtotal'         => $subtotal,
                    'shipping_fee'     => $shippingFee,
                    'coupon_id'        => $couponRecord?->id,
                    'coupon_code'      => $couponCode,
                    'discount_amount'  => $totalDiscount + $loyaltyDiscount,
                    'grand_total'      => $grandTotal,
                    'total_amount'     => $grandTotal,
                    'currency'         => 'BDT',
                    'payment_method'   => $this->paymentMethod,
                    'payment_gateway'  => ($this->paymentMethod === 'bkash') ? 'bkash' : (($this->paymentMethod === 'online') ? 'sslcommerz' : null),
                    'payment_status'   => 'pending',
                    'order_status'     => 'pending',
                ]);

                // Redeem Loyalty Points if used
                if ($loyaltyPointsUsed > 0) {
                    \App\Services\LoyaltyService::redeemPoints(
                        auth()->user(),
                        $loyaltyPointsUsed,
                        "Redeemed on Order #{$createdOrder->order_number}",
                        $createdOrder->id
                    );
                }

                // Record coupon usage and increment redemptions count
                if ($couponRecord && $promoDiscount > 0) {
                    DiscountService::recordUsage(
                        $couponRecord,
                        $createdOrder,
                        $promoDiscount,
                        auth()->id(),
                        $this->phone
                    );
                }

                foreach ($validatedItems as $itemData) {
                    /** @var Product $product */
                    $product = $itemData['product'];
                    $quantity = $itemData['quantity'];
                    $unitPrice = $itemData['unitPrice'];
                    $itemTotal = $itemData['itemTotal'];

                    $varNameStr = !empty($itemData['variation_name']) ? ' (' . $itemData['variation_name'] . ')' : '';
                    OrderItem::create([
                        'order_id'     => $createdOrder->id,
                        'product_id'   => $product->id,
                        'product_name' => $product->name . $varNameStr,
                        'price'        => $unitPrice,
                        'unit_price'   => $unitPrice,
                        'quantity'     => $quantity,
                        'total'        => $itemTotal,
                        'total_price'  => $itemTotal,
                    ]);

                    // If product has an active flash deal, increment sold_count
                    $flashDeal = $product->getActiveFlashSaleDeal();
                    if ($flashDeal) {
                        $flashDeal->increment('sold_count', $quantity);
                    }

                    // Atomically decrement stock from database
                    $product->decrement('stock_quantity', $quantity);
                }

                // Clear cart and coupon sessions
                session()->forget('cart');
                session()->forget('applied_coupon');

                return $createdOrder;
            });

            // 3. bKash Direct Tokenized Checkout Flow
            if ($this->paymentMethod === 'bkash') {
                $bkashUrl = BkashService::createPayment($order);
                return redirect()->away($bkashUrl);
            }

            // 4. SSLCommerz Online Payment Gateway Flow
            if ($this->paymentMethod === 'online') {
                $paymentUrl = SSLCommerzService::initiatePayment($order);
                return redirect()->away($paymentUrl);
            }

            // 5. Notify Store Admins & Customer
            \App\Services\OrderNotificationService::notifyAdminsNewOrder($order);
            \App\Services\OrderNotificationService::notifyCustomerOrderPlaced($order);

            return redirect()->route('order.success', ['orderNumber' => $order->order_number]);

        } catch (Exception $e) {
            session()->flash('error', 'Order processing failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $cart = CartService::getCart();
        $subtotal = CartService::getTotal();

        // Applied Promo Coupon
        $appliedCoupon = session('applied_coupon', null);
        $promoDiscount = $appliedCoupon ? (float) $appliedCoupon['discount'] : 0.0;

        // Automatic Spending Tier Discount (Admin Rule)
        $autoDiscountData = SettingService::calculateAutoSpendingDiscount($subtotal);
        $autoDiscountAmount = $autoDiscountData['active'] ? (float) $autoDiscountData['discount'] : 0.0;

        $totalDiscount = min($subtotal, $promoDiscount + $autoDiscountAmount);

        // Real-Time Delivery Fee Calculation
        $this->shippingFee = SettingService::calculateShippingFee($subtotal, $this->city);
        $isFreeShipping = ($this->shippingFee == 0.0);

        // Calculate Loyalty Discount (if toggled)
        $loyaltyDiscount = 0.0;
        $loyaltyPointsUsed = 0;
        if ($this->useLoyaltyPoints && auth()->check() && SettingService::isLoyaltyEnabled()) {
            $userPoints = auth()->user()->points_balance;
            $pointValue = SettingService::getLoyaltyRedemptionValue();
            $maxPointsNeeded = (int) ceil(($subtotal - $totalDiscount + $this->shippingFee) / $pointValue);
            
            $loyaltyPointsUsed = min($userPoints, $maxPointsNeeded);
            $loyaltyDiscount = $loyaltyPointsUsed * $pointValue;
        }

        $grandTotal = max(0, ($subtotal - $totalDiscount - $loyaltyDiscount) + $this->shippingFee);

        return view('livewire.checkout-page', [
            'cart'               => $cart,
            'subtotal'           => $subtotal,
            'promoDiscount'      => $promoDiscount,
            'autoDiscountData'   => $autoDiscountData,
            'autoDiscountAmount' => $autoDiscountAmount,
            'loyaltyDiscount'    => $loyaltyDiscount,
            'loyaltyPointsUsed'  => $loyaltyPointsUsed,
            'totalDiscount'      => $totalDiscount + $loyaltyDiscount,
            'appliedCoupon'      => $appliedCoupon,
            'shippingFee'        => $this->shippingFee,
            'isFreeShipping'     => $isFreeShipping,
            'grandTotal'         => $grandTotal,
        ]);
    }
}