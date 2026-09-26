<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'type', // fixed, percentage
        'value',
        'min_spend',
        'max_discount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value'                => 'decimal:2',
        'min_spend'            => 'decimal:2',
        'max_discount'         => 'decimal:2',
        'starts_at'            => 'datetime',
        'expires_at'           => 'datetime',
        'is_active'            => 'boolean',
        'usage_limit'          => 'integer',
        'usage_limit_per_user' => 'integer',
        'used_count'           => 'integer',
    ];

    /**
     * Orders that used this coupon.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Usage history log for this coupon.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Check if coupon validity has not started yet.
     */
    public function hasNotStarted(): bool
    {
        return $this->starts_at && Carbon::now()->lt($this->starts_at);
    }

    /**
     * Check if coupon has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && Carbon::now()->gt($this->expires_at);
    }

    /**
     * Check if storewide usage limit is reached.
     */
    public function isUsageLimitReached(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    /**
     * Check if user/phone specific limit has been reached.
     */
    public function isUserLimitReached(?int $userId = null, ?string $phone = null): bool
    {
        if ($this->usage_limit_per_user === null) {
            return false;
        }

        $query = $this->usages();

        if ($userId) {
            $count = (clone $query)->where('user_id', $userId)->count();
            if ($count >= $this->usage_limit_per_user) {
                return true;
            }
        }

        if (!empty($phone)) {
            $count = (clone $query)->where('customer_phone', $phone)->count();
            if ($count >= $this->usage_limit_per_user) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate discount amount for a given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percentage') {
            $discount = round(($subtotal * (float) $this->value) / 100, 2);
            if ($this->max_discount !== null && $this->max_discount > 0) {
                $discount = min($discount, (float) $this->max_discount);
            }
        } else {
            // Fixed discount
            $discount = (float) $this->value;
        }

        return max(0, min($discount, $subtotal));
    }

    /**
     * Validate whether this coupon can be applied.
     */
    public function validateCoupon(float $subtotal, ?int $userId = null, ?string $phone = null): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => "Coupon '{$this->code}' is currently inactive."];
        }

        if ($this->hasNotStarted()) {
            return ['valid' => false, 'message' => "Coupon '{$this->code}' starts on " . $this->starts_at->format('M d, Y h:i A') . "."];
        }

        if ($this->isExpired()) {
            return ['valid' => false, 'message' => "Coupon '{$this->code}' expired on " . $this->expires_at->format('M d, Y') . "."];
        }

        if ($this->isUsageLimitReached()) {
            return ['valid' => false, 'message' => "Coupon '{$this->code}' usage limit has been reached."];
        }

        if ($this->min_spend > 0 && $subtotal < $this->min_spend) {
            return ['valid' => false, 'message' => "Minimum spend of BDT " . number_format($this->min_spend, 2) . " required for '{$this->code}'."];
        }

        if ($this->isUserLimitReached($userId, $phone)) {
            return ['valid' => false, 'message' => "You have already reached the maximum usage limit for coupon '{$this->code}'."];
        }

        $discount = $this->calculateDiscount($subtotal);

        return [
            'valid'    => true,
            'message'  => "Coupon '{$this->code}' applied successfully!",
            'discount' => $discount,
        ];
    }

    /**
     * Formatted string label for discount value.
     */
    public function getFormattedDiscountAttribute(): string
    {
        if ($this->type === 'percentage') {
            $str = rtrim(rtrim(number_format($this->value, 2), '0'), '.') . '% OFF';
            if ($this->max_discount > 0) {
                $str .= ' (Max BDT ' . number_format($this->max_discount) . ')';
            }
            return $str;
        }

        return 'BDT ' . number_format($this->value, 2) . ' OFF';
    }

    /**
     * Scope active coupons only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
