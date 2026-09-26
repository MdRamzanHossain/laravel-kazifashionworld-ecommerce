<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\ThemeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageTheme extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'Storefront & Content';

    protected static ?string $navigationLabel = 'Theme & Appearance';

    protected static ?string $title = 'Storefront Theme & Branding Customizer';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.manage-theme';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = ThemeService::getThemeSettings();

        $this->form->fill([
            'theme_preset'              => $settings['theme_preset'],
            'theme_primary_color'       => $settings['primary_color'],
            'theme_primary_hover'       => $settings['primary_hover'],
            'theme_accent_color'        => $settings['accent_color'],
            'theme_accent_light'        => $settings['accent_light'],
            'theme_announcement_bg'     => $settings['announcement_bg'],
            'theme_announcement_text'   => $settings['announcement_text'],
            'theme_header_glass_bg'     => $settings['header_glass_bg'],
            'theme_footer_bg'           => $settings['footer_bg'],
            'theme_heading_font'        => $settings['heading_font'],
            'theme_body_font'           => $settings['body_font'],
            'theme_card_radius'         => $settings['card_radius'],
            'theme_card_shadow'         => $settings['card_shadow'],
            
            // Store Identity
            'theme_store_name'          => $settings['store_name'],
            'theme_store_tagline'       => $settings['store_tagline'],
            'theme_logo_image'          => $settings['logo_image'],
            'theme_favicon_image'       => $settings['favicon_image'],
            
            // Layout & Interactivity
            'theme_navbar_hide_on_scroll'     => $settings['navbar_hide_on_scroll'],
            'theme_whatsapp_floating_enabled' => $settings['whatsapp_floating_enabled'],
            'theme_whatsapp_number'           => $settings['whatsapp_number'],
            'theme_whatsapp_greeting'         => $settings['whatsapp_greeting'],
            'theme_social_proof_enabled'      => $settings['social_proof_enabled'],
            
            // Custom CSS
            'theme_custom_css'          => $settings['custom_css'],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('1-Click Luxury Theme Presets')
                    ->description('Select from carefully crafted haute couture palettes or create your own custom theme.')
                    ->schema([
                        Forms\Components\Select::make('theme_preset')
                            ->label('Select Theme Preset')
                            ->options([
                                'rose_luxury'         => '🌸 Rose Gold Luxury (Default Luxury Beauty)',
                                'midnight_gold'       => '🌙 Midnight Couture & Amber Gold (Opulent Dark)',
                                'emerald_velvet'      => '🌿 Emerald Velvet & Champagne (Royal Botanical)',
                                'royal_violet'        => '👑 Royal Violet & Radiant Gold (Majestic)',
                                'parisian_monochrome' => '🖤 Parisian Haute Monochrome (Minimalist B&W)',
                                'custom'              => '🎨 Custom Palette (Manual Configuration)',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state !== 'custom') {
                                    $presets = ThemeService::getPresets();
                                    if (isset($presets[$state])) {
                                        $p = $presets[$state];
                                        $set('theme_primary_color', $p['primary_color']);
                                        $set('theme_primary_hover', $p['primary_hover']);
                                        $set('theme_accent_color', $p['accent_color']);
                                        $set('theme_accent_light', $p['accent_light']);
                                        $set('theme_announcement_bg', $p['announcement_bg']);
                                        $set('theme_announcement_text', $p['announcement_text']);
                                        $set('theme_header_glass_bg', $p['header_glass_bg']);
                                        $set('theme_footer_bg', $p['footer_bg']);
                                        $set('theme_heading_font', $p['heading_font']);
                                        $set('theme_body_font', $p['body_font']);
                                        $set('theme_card_radius', $p['card_radius']);
                                        $set('theme_card_shadow', $p['card_shadow']);
                                    }
                                }
                            })
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Color Palette & Styling')
                    ->description('Fine-tune primary brand tones, accents, and section backgrounds.')
                    ->schema([
                        Forms\Components\ColorPicker::make('theme_primary_color')
                            ->label('Primary Brand Color')
                            ->helperText('Used for primary buttons, highlights, and icons.')
                            ->required(),

                        Forms\Components\ColorPicker::make('theme_primary_hover')
                            ->label('Primary Hover Tone')
                            ->helperText('Slightly darker shade for interactive states.')
                            ->required(),

                        Forms\Components\ColorPicker::make('theme_accent_color')
                            ->label('Accent Highlight Color')
                            ->helperText('Badges, discount pills, and focus rings.')
                            ->required(),

                        Forms\Components\ColorPicker::make('theme_announcement_bg')
                            ->label('Top Announcement Bar BG')
                            ->required(),

                        Forms\Components\ColorPicker::make('theme_announcement_text')
                            ->label('Announcement Bar Text')
                            ->required(),

                        Forms\Components\ColorPicker::make('theme_footer_bg')
                            ->label('Footer Background Color')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Typography & Font Pairing')
                    ->description('Choose clean sans-serif or editorial serif typography for headings and body.')
                    ->schema([
                        Forms\Components\Select::make('theme_heading_font')
                            ->label('Headings Font (Titles & Banners)')
                            ->options([
                                "'Plus Jakarta Sans', sans-serif"   => 'Plus Jakarta Sans (Ultra-Clean Sans - Modern Luxury)',
                                "'Montserrat', sans-serif"          => 'Montserrat (Geometric Haute Couture)',
                                "'Playfair Display', serif"         => 'Playfair Display (Editorial Luxury Serif)',
                                "'Cormorant Garamond', serif"       => 'Cormorant Garamond (Royal French Serif)',
                                "'Cinzel', serif"                   => 'Cinzel (Imperial Classical Serif)',
                                "'Inter', sans-serif"               => 'Inter (Minimalist Clean)',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('theme_body_font')
                            ->label('Body Copy Font')
                            ->options([
                                "'Plus Jakarta Sans', sans-serif" => 'Plus Jakarta Sans (Default)',
                                "'Inter', sans-serif"             => 'Inter',
                                "'Geist', sans-serif"             => 'Geist',
                            ])
                            ->required()
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Store Identity & Assets')
                    ->description('Customize store naming and upload custom logo / favicon.')
                    ->schema([
                        Forms\Components\TextInput::make('theme_store_name')
                            ->label('Brand / Store Name')
                            ->placeholder('e.g. KAZI FASHION')
                            ->required(),

                        Forms\Components\TextInput::make('theme_store_tagline')
                            ->label('Monogram / Subtitle')
                            ->placeholder('e.g. Luxury World')
                            ->required(),

                        Forms\Components\FileUpload::make('theme_logo_image')
                            ->label('Custom Header Logo Image (Optional)')
                            ->image()
                            ->disk('public')
                            ->directory('theme')
                            ->helperText('If left empty, the standard logo is displayed.'),

                        Forms\Components\FileUpload::make('theme_favicon_image')
                            ->label('Custom Favicon Icon (Optional)')
                            ->image()
                            ->disk('public')
                            ->directory('theme')
                            ->helperText('Square 32x32 or 64x64 PNG icon.'),
                    ])->columns(2),

                Forms\Components\Section::make('Product Cards & Interactive Layout')
                    ->description('Control component roundness, elevation shadows, and auto-hiding behavior.')
                    ->schema([
                        Forms\Components\Select::make('theme_card_radius')
                            ->label('Card Border Radius')
                            ->options([
                                'rounded-3xl' => 'Curved Luxury (3XL - Default)',
                                'rounded-2xl' => 'Soft Modern (2XL)',
                                'rounded-xl'  => 'Crisp Contemporary (XL)',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('theme_card_shadow')
                            ->label('Card Elevation Shadow')
                            ->options([
                                'shadow-sm hover:shadow-2xl' => 'Subtle Floating (Default)',
                                'shadow-md hover:shadow-2xl' => 'Prominent 3D Glow',
                                'shadow-none border'         => 'Flat Minimalist Border',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Toggle::make('theme_navbar_hide_on_scroll')
                            ->label('Smart Hide Navbar on Scroll')
                            ->helperText('Smoothly slides navbar away on downward scroll and reveals on scroll up.')
                            ->default(true),

                        Forms\Components\Toggle::make('theme_social_proof_enabled')
                            ->label('Enable Social Proof Notifications')
                            ->helperText('Displays non-intrusive live verified purchase popups.')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Floating WhatsApp Helpline Widget')
                    ->description('Provide visitors with a direct one-tap customer care concierge.')
                    ->schema([
                        Forms\Components\Toggle::make('theme_whatsapp_floating_enabled')
                            ->label('Show Floating WhatsApp Button')
                            ->default(true),

                        Forms\Components\TextInput::make('theme_whatsapp_number')
                            ->label('WhatsApp Phone Number (with Country Code)')
                            ->placeholder('e.g. 8801735940279')
                            ->required(),

                        Forms\Components\TextInput::make('theme_whatsapp_greeting')
                            ->label('Default Pre-filled Message')
                            ->placeholder('Hello! I would like to inquire about your products.')
                            ->columnSpanFull()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Advanced Custom CSS Injections')
                    ->description('Add custom styling rules directly to the customer-facing storefront.')
                    ->schema([
                        Forms\Components\Textarea::make('theme_custom_css')
                            ->label('Custom CSS Code')
                            ->placeholder("/* Add your custom CSS here */\n.luxury-badge { letter-spacing: 0.2em; }")
                            ->rows(6)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            Setting::set($key, $value, 'theme');
        }

        // Clear dynamic theme caches
        ThemeService::clearCache();

        Notification::make()
            ->title('Theme & Branding Saved Successfully!')
            ->body('Storefront colors, typography, layout, and styling have been updated in real-time.')
            ->success()
            ->send();
    }
}