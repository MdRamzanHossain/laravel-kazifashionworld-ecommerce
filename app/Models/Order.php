<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payment_details' => 'array',
    ];

    /**
     * Get the items for the order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the coupon applied to this order.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the SMS logs associated with this order.
     */
    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    /**
     * Get the shortened tracking URLs for this order.
     */
    public function shortUrls(): HasMany
    {
        return $this->hasMany(ShortUrl::class);
    }

    /**
     * Get the payment transactions associated with this order.
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Helper to resolve the best customer email address.
     */
    public function getEmailRecipient(): ?string
    {
        return $this->customer_email ?: $this->user?->email;
    }
}