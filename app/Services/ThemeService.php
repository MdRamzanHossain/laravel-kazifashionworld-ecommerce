<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    /**
     * Pre-configured Luxury Theme Presets
     */
    public static function getPresets(): array
    {
        return [
            'rose_luxury' => [
                'name'               => 'Rose Gold Luxury (Default)',
                'description'        => 'Romantic luxury palette with deep rose tones, luminous accents, and clean modern typography.',
                'primary_color'      => '#e11d48',
                'primary_hover'      => '#be123c',
                'accent_color'       => '#f43f5e',
                'accent_light'       => '#fff1f2',
                'announcement_bg'    => '#09090b',
                'announcement_text'  => '#ffffff',
                'header_glass_bg'    => 'rgba(255, 255, 255, 0.85)',
                'footer_bg'          => '#09090b',
                'heading_font'       => "'Plus Jakarta Sans', sans-serif",
                'body_font'          => "'Plus Jakarta Sans', sans-serif",
                'card_radius'        => 'rounded-3xl',
                'card_shadow'        => 'shadow-sm hover:shadow-2xl',
            ],
            'midnight_gold' => [
                'name'               => 'Midnight Couture & Amber Gold',
                'description'        => 'Opulent dark luxury theme with shimmering amber gold accents for premium couture.',
                'primary_color'      => '#d97706',
                'primary_hover'      => '#b45309',
                'accent_color'       => '#f59e0b',
                'accent_light'       => '#fffbeb',
                'announcement_bg'    => '#020617',
                'announcement_text'  => '#fbbf24',
                'header_glass_bg'    => 'rgba(255, 255, 255, 0.90)',
                'footer_bg'          => '#020617',
                'heading_font'       => "'Playfair Display', serif",
                'body_font'          => "'Plus Jakarta Sans', sans-serif",
                'card_radius'        => 'rounded-3xl',
                'card_shadow'        => 'shadow-md hover:shadow-2xl',
            ],
            'emerald_velvet' => [
                'name'               => 'Emerald Velvet & Champagne',
                'description'        => 'Royal botanical luxury theme with deep emerald green and sparkling champagne accents.',
                'primary_color'      => '#059669',
                'primary_hover'      => '#047857',
                'accent_color'       => '#10b981',
                'accent_light'       => '#ecfdf5',
                'announcement_bg'    => '#064e3b',
                'announcement_text'  => '#ffffff',
                'header_glass_bg'    => 'rgba(255, 255, 255, 0.85)',
                'footer_bg'          => '#064e3b',
                'heading_font'       => "'Cormorant Garamond', serif",
                'body_font'          => "'Plus Jakarta Sans', sans-serif",
                'card_radius'        => 'rounded-3xl',
                'card_shadow'        => 'shadow-sm hover:shadow-xl',
            ],
            'royal_violet' => [
                'name'               => 'Royal Violet & Radiant Gold',
                'description'        => 'Majestic violet couture palette with luminous golden highlights.',
                'primary_color'      => '#7c3aed',
                'primary_hover'      => '#6d28d9',
                'accent_color'       => '#8b5cf6',
                'accent_light'       => '#f5f3ff',
                'announcement_bg'    => '#1e1b4b',
                'announcement_text'  => '#fef08a',
                'header_glass_bg'    => 'rgba(255, 255, 255, 0.85)',
                'footer_bg'          => '#1e1b4b',
                'heading_font'       => "'Cinzel', serif",
                'body_font'          => "'Plus Jakarta Sans', sans-serif",
                'card_radius'        => 'rounded-3xl',
                'card_shadow'        => 'shadow-md hover:shadow-2xl',
            ],
            'parisian_monochrome' => [
                'name'               => 'Parisian Haute Monochrome',
                'description'        => 'Ultra-minimalist haute couture editorial black & white aesthetic.',
                'primary_color'      => '#18181b',
                'primary_hover'      => '#09090b',
                'accent_color'       => '#27272a',
                'accent_light'       => '#f4f4f5',
                'announcement_bg'    => '#09090b',
                'announcement_text'  => '#ffffff',
                'header_glass_bg'    => 'rgba(255, 255, 255, 0.95)',
                'footer_bg'          => '#09090b',
                'heading_font'       => "'Montserrat', sans-serif",
                'body_font'          => "'Inter', sans-serif",
                'card_radius'        => 'rounded-2xl',
                'card_shadow'        => 'shadow-sm hover:shadow-lg',
            ],
        ];
    }

    /**
     * Get all active theme settings with defaults
     */
    public static function getThemeSettings(): array
    {
        return Cache::rememberForever('active_storefront_theme_settings', function () {
            $presetKey = Setting::get('theme_preset', 'rose_luxury');
            $presets = self::getPresets();
            $defaultPreset = $presets[$presetKey] ?? $presets['rose_luxury'];

            return [
                'theme_preset'              => $presetKey,
                'primary_color'             => Setting::get('theme_primary_color', $defaultPreset['primary_color']),
                'primary_hover'             => Setting::get('theme_primary_hover', $defaultPreset['primary_hover']),
                'accent_color'              => Setting::get('theme_accent_color', $defaultPreset['accent_color']),
                'accent_light'              => Setting::get('theme_accent_light', $defaultPreset['accent_light']),
                'announcement_bg'           => Setting::get('theme_announcement_bg', $defaultPreset['announcement_bg']),
                'announcement_text'         => Setting::get('theme_announcement_text', $defaultPreset['announcement_text']),
                'header_glass_bg'           => Setting::get('theme_header_glass_bg', $defaultPreset['header_glass_bg']),
                'footer_bg'                 => Setting::get('theme_footer_bg', $defaultPreset['footer_bg']),
                'heading_font'              => Setting::get('theme_heading_font', $defaultPreset['heading_font']),
                'body_font'                 => Setting::get('theme_body_font', $defaultPreset['body_font']),
                'card_radius'               => Setting::get('theme_card_radius', $defaultPreset['card_radius']),
                'card_shadow'               => Setting::get('theme_card_shadow', $defaultPreset['card_shadow']),
                
                // Store Identity & Branding
                'store_name'                => Setting::get('theme_store_name', 'KAZI FASHION'),
                'store_tagline'             => Setting::get('theme_store_tagline', 'Luxury World'),
                'logo_image'                => Setting::get('theme_logo_image', null),
                'favicon_image'             => Setting::get('theme_favicon_image', null),
                
                // Interactive Widgets
                'navbar_hide_on_scroll'     => (bool) filter_var(Setting::get('theme_navbar_hide_on_scroll', true), FILTER_VALIDATE_BOOLEAN),
                'whatsapp_floating_enabled' => (bool) filter_var(Setting::get('theme_whatsapp_floating_enabled', true), FILTER_VALIDATE_BOOLEAN),
                'whatsapp_number'           => Setting::get('theme_whatsapp_number', '8801735940279'),
                'whatsapp_greeting'         => Setting::get('theme_whatsapp_greeting', 'Hello Kazi Fashion! I would like to inquire about your luxury collections.'),
                'social_proof_enabled'      => (bool) filter_var(Setting::get('theme_social_proof_enabled', true), FILTER_VALIDATE_BOOLEAN),
                
                // Custom CSS
                'custom_css'                => Setting::get('theme_custom_css', ''),
            ];
        });
    }

    /**
     * Clear Theme Cache
     */
    public static function clearCache(): void
    {
        Cache::forget('active_storefront_theme_settings');
    }

    /**
     * Generate Dynamic CSS Variables and Styles for Storefront `<head>`
     */
    public static function renderDynamicThemeStyles(): string
    {
        $t = self::getThemeSettings();

        $primary = htmlspecialchars($t['primary_color'], ENT_QUOTES, 'UTF-8');
        $primaryHover = htmlspecialchars($t['primary_hover'], ENT_QUOTES, 'UTF-8');
        $accent = htmlspecialchars($t['accent_color'], ENT_QUOTES, 'UTF-8');
        $accentLight = htmlspecialchars($t['accent_light'], ENT_QUOTES, 'UTF-8');
        $headingFont = $t['heading_font'];
        $bodyFont = $t['body_font'];
        $announcementBg = htmlspecialchars($t['announcement_bg'], ENT_QUOTES, 'UTF-8');
        $announcementText = htmlspecialchars($t['announcement_text'], ENT_QUOTES, 'UTF-8');
        $footerBg = htmlspecialchars($t['footer_bg'], ENT_QUOTES, 'UTF-8');
        $customCss = $t['custom_css'];

        return <<<CSS
        <style id="custom-theme-variables">
            :root {
                --color-brand-primary: {$primary};
                --color-brand-primary-hover: {$primaryHover};
                --color-brand-accent: {$accent};
                --color-brand-accent-light: {$accentLight};
                --font-brand-heading: {$headingFont};
                --font-brand-body: {$bodyFont};
            }

            /* Dynamic Theme Typography Overrides */
            h1, h2, h3, h4, h5, h6, .font-heading {
                font-family: var(--font-brand-heading) !important;
            }

            body, .font-sans {
                font-family: var(--font-brand-body) !important;
            }

            /* Dynamic Theme Brand Colors */
            .text-brand-600, .text-brand-700, .hover\:text-brand-600:hover {
                color: var(--color-brand-primary) !important;
            }

            .bg-brand-600, .hover\:bg-brand-600:hover, .bg-brand-700, .hover\:bg-brand-700:hover {
                background-color: var(--color-brand-primary) !important;
            }

            .border-brand-600, .hover\:border-brand-600:hover, .border-brand-500 {
                border-color: var(--color-brand-primary) !important;
            }

            .ring-brand-500, .ring-brand-600 {
                --tw-ring-color: var(--color-brand-primary) !important;
            }

            /* Top Announcement Bar Colors */
            .theme-announcement-bar {
                background-color: {$announcementBg} !important;
                color: {$announcementText} !important;
            }

            /* Footer Background */
            .theme-footer-bg {
                background-color: {$footerBg} !important;
            }

            /* Custom Admin Injected CSS */
            {$customCss}
        </style>
        CSS;
    }
}