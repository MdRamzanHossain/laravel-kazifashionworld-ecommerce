<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    /**
     * Get free delivery threshold amount (default BDT 1,500).
     */
    public static function getFreeShippingThreshold(): float
    {
        return (float) Setting::get('free_shipping_min_spend', 1500.00);
    }

    /**
     * Check if automatic free shipping is enabled.
     */
    public static function isFreeShippingEnabled(): bool
    {
        return (bool) filter_var(Setting::get('free_shipping_enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get free shipping applicability scope ('inside_dhaka' or 'all').
     */
    public static function getFreeShippingScope(): string
    {
        return (string) Setting::get('free_shipping_scope', 'inside_dhaka');
    }

    /**
     * Get standard base delivery fees for inside and outside Dhaka.
     */
    public static function getStandardShippingFees(): array
    {
        return [
            'inside_dhaka'  => (float) Setting::get('inside_dhaka_fee', 80.00),
            'outside_dhaka' => (float) Setting::get('outside_dhaka_fee', 150.00),
        ];
    }

    /**
     * Calculate exact delivery fee for a given cart subtotal and destination city.
     */
    public static function calculateShippingFee(float $subtotal, string $city): float
    {
        $fees = self::getStandardShippingFees();
        $baseFee = ($city === 'inside_dhaka') ? $fees['inside_dhaka'] : $fees['outside_dhaka'];

        if (self::isFreeShippingEnabled()) {
            $threshold = self::getFreeShippingThreshold();
            $scope = self::getFreeShippingScope();

            if ($subtotal >= $threshold) {
                if ($scope === 'all' || ($scope === 'inside_dhaka' && $city === 'inside_dhaka')) {
                    return 0.00; // Free delivery unlocked!
                }
            }
        }

        return $baseFee;
    }

    /**
     * Check and calculate automatic spending tier discount (if enabled).
     */
    public static function calculateAutoSpendingDiscount(float $subtotal): array
    {
        $enabled = (bool) filter_var(Setting::get('auto_discount_enabled', false), FILTER_VALIDATE_BOOLEAN);

        if (!$enabled) {
            return ['active' => false, 'discount' => 0.0, 'name' => '', 'message' => ''];
        }

        $minSpend = (float) Setting::get('auto_discount_min_spend', 5000.00);
        $type = (string) Setting::get('auto_discount_type', 'fixed');
        $value = (float) Setting::get('auto_discount_value', 300.00);
        $name = (string) Setting::get('auto_discount_name', 'Automatic Spend Bonus');

        if ($subtotal >= $minSpend) {
            $discount = ($type === 'percentage') ? round(($subtotal * $value) / 100, 2) : $value;
            $discount = max(0, min($discount, $subtotal));

            return [
                'active'   => true,
                'discount' => $discount,
                'name'     => $name,
                'message'  => "{$name}: Saved BDT " . number_format($discount, 2) . " on order over BDT " . number_format($minSpend, 2) . "!",
            ];
        }

        return ['active' => false, 'discount' => 0.0, 'name' => $name, 'message' => ''];
    }

    /**
     * Get top announcement bar banner text.
     */
    public static function getAnnouncementText(): string
    {
        $threshold = number_format(self::getFreeShippingThreshold());
        $default = "✨ Eid & Festive Collection Live • Free Inside Dhaka Delivery on Orders Over BDT {$threshold}!";
        return (string) Setting::get('announcement_bar_text', $default);
    }

    /**
     * Get all customizable settings for the Storefront Hero Banner.
     */
    public static function getHeroSettings(): array
    {
        return [
            'badge_text'           => (string) Setting::get('hero_badge_text', 'dYO, 2026 Luxury Beauty & Festive Apparel'),
            'title_prefix'         => (string) Setting::get('hero_title_prefix', 'Reveal Your'),
            'title_highlight'      => (string) Setting::get('hero_title_highlight', 'True Elegance'),
            'title_suffix'         => (string) Setting::get('hero_title_suffix', '& Glow.'),
            'description'          => (string) Setting::get('hero_description', 'Discover 100% authentic international skincare, viral cosmetics, and handcrafted couture attire curated for every skin tone & occasion.'),
            'primary_btn_text'     => (string) Setting::get('hero_primary_btn_text', 'Shop Best Sellers +'),
            'primary_btn_url'      => (string) Setting::get('hero_primary_btn_url', '#products-section'),
            'secondary_btn_text'   => (string) Setting::get('hero_secondary_btn_text', 'Track Delivery'),
            'secondary_btn_url'    => (string) Setting::get('hero_secondary_btn_url', '/track-order'),
            'stat1_value'          => (string) Setting::get('hero_stat1_value', '100%'),
            'stat1_label'          => (string) Setting::get('hero_stat1_label', 'Authentic Guaranteed'),
            'stat2_value'          => (string) Setting::get('hero_stat2_value', '5,000+'),
            'stat2_label'          => (string) Setting::get('hero_stat2_label', 'Happy Customers'),
            'stat3_value'          => (string) Setting::get('hero_stat3_value', '24-48h'),
            'stat3_label'          => (string) Setting::get('hero_stat3_label', 'Express Delivery'),
            'show_featured_products'=> filter_var(Setting::get('hero_show_featured_products', true), FILTER_VALIDATE_BOOLEAN),
            'carousel_slides'      => json_decode(Setting::get('hero_carousel_slides', '[]'), true) ?? [],
        ];
    }

    /**
     * Get Loyalty Program Settings
     */
    public static function isLoyaltyEnabled(): bool
    {
        return (bool) filter_var(Setting::get('loyalty_points_enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    public static function getLoyaltyPointsPer100Spend(): float
    {
        return (float) Setting::get('loyalty_points_per_spend', 1);
    }

    public static function getLoyaltyPointsPerReview(): float
    {
        return (float) Setting::get('loyalty_points_per_review', 50);
    }

    public static function getLoyaltyRedemptionValue(): float
    {
        return (float) Setting::get('loyalty_points_redemption_value', 1.00);
    }

    /**
     * Get Analytics Tracking IDs
     */
    public static function getMetaPixelId(): ?string
    {
        return Setting::get('meta_pixel_id', null);
    }

    public static function getGa4MeasurementId(): ?string
    {
        return Setting::get('ga4_measurement_id', null);
    }
}
