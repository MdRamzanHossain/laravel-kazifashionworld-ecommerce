<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\SettingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageHeroBanner extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Storefront & Content';

    protected static ?string $navigationLabel = 'Hero Banner Customizer';

    protected static ?string $title = 'Storefront Hero Banner Management';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-hero-banner';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SettingService::getHeroSettings();

        $this->form->fill([
            'hero_badge_text'         => $settings['badge_text'],
            'hero_title_prefix'       => $settings['title_prefix'],
            'hero_title_highlight'    => $settings['title_highlight'],
            'hero_title_suffix'       => $settings['title_suffix'],
            'hero_description'        => $settings['description'],
            'hero_primary_btn_text'   => $settings['primary_btn_text'],
            'hero_primary_btn_url'    => $settings['primary_btn_url'],
            'hero_secondary_btn_text' => $settings['secondary_btn_text'],
            'hero_secondary_btn_url'  => $settings['secondary_btn_url'],
            'hero_stat1_value'        => $settings['stat1_value'],
            'hero_stat1_label'        => $settings['stat1_label'],
            'hero_stat2_value'        => $settings['stat2_value'],
            'hero_stat2_label'        => $settings['stat2_label'],
            'hero_stat3_value'        => $settings['stat3_value'],
            'hero_stat3_label'        => $settings['stat3_label'],
            'hero_show_featured_products' => $settings['show_featured_products'] ?? true,
            'hero_carousel_slides'    => $settings['carousel_slides'],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Luxury Headline & Typography')
                    ->description('Customize the main editorial copy shown at the top of the homepage.')
                    ->schema([
                        Forms\Components\TextInput::make('hero_badge_text')
                            ->label('Top Glowing Pill Badge')
                            ->placeholder('e.g. ✨ 2026 Luxury Beauty & Festive Apparel')
                            ->columnSpanFull()
                            ->required(),

                        Forms\Components\TextInput::make('hero_title_prefix')
                            ->label('Title Prefix')
                            ->placeholder('e.g. Reveal Your')
                            ->required(),

                        Forms\Components\TextInput::make('hero_title_highlight')
                            ->label('Title Highlight (Italic Serif)')
                            ->placeholder('e.g. True Elegance')
                            ->helperText('Rendered in luxury italic serif with glowing brand color.')
                            ->required(),

                        Forms\Components\TextInput::make('hero_title_suffix')
                            ->label('Title Suffix')
                            ->placeholder('e.g. & Glow.')
                            ->required(),

                        Forms\Components\Textarea::make('hero_description')
                            ->label('Hero Description Subtitle')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Call-To-Action (CTA) Action Buttons')
                    ->description('Direct visitors to featured collections, product catalogs, or tracking.')
                    ->schema([
                        Forms\Components\TextInput::make('hero_primary_btn_text')
                            ->label('Primary Button Text')
                            ->placeholder('e.g. Shop Best Sellers →')
                            ->required(),

                        Forms\Components\TextInput::make('hero_primary_btn_url')
                            ->label('Primary Button URL / Anchor')
                            ->placeholder('e.g. #products-section or /')
                            ->required(),

                        Forms\Components\TextInput::make('hero_secondary_btn_text')
                            ->label('Secondary Button Text')
                            ->placeholder('e.g. Track Delivery')
                            ->required(),

                        Forms\Components\TextInput::make('hero_secondary_btn_url')
                            ->label('Secondary Button URL')
                            ->placeholder('e.g. /track-order')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Trust Proof Statistics Counters (3 Pillars)')
                    ->description('Social proof stats displayed directly below the CTA buttons.')
                    ->schema([
                        Forms\Components\TextInput::make('hero_stat1_value')
                            ->label('Stat 1 Value')
                            ->placeholder('e.g. 100%')
                            ->required(),

                        Forms\Components\TextInput::make('hero_stat1_label')
                            ->label('Stat 1 Label')
                            ->placeholder('e.g. Authentic Guaranteed')
                            ->required(),

                        Forms\Components\TextInput::make('hero_stat2_value')
                            ->label('Stat 2 Value')
                            ->placeholder('e.g. 5,000+')
                            ->required(),

                        Forms\Components\TextInput::make('hero_stat2_label')
                            ->label('Stat 2 Label')
                            ->placeholder('e.g. Happy Customers')
                            ->required(),

                        Forms\Components\TextInput::make('hero_stat3_value')
                            ->label('Stat 3 Value')
                            ->placeholder('e.g. 24-48h')
                            ->required(),

                        Forms\Components\TextInput::make('hero_stat3_label')
                            ->label('Stat 3 Label')
                            ->placeholder('e.g. Express Delivery')
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Premium Hero Carousel (Right Side)')
                    ->description('Manage custom slides for the new 3D rotating carousel. These will appear alongside your dynamically featured products.')
                    ->schema([
                        Forms\Components\Toggle::make('hero_show_featured_products')
                            ->label('Show Automatic Featured Products')
                            ->helperText('If enabled, the carousel will automatically pull your top 3 featured products and display them after your custom slides. Turn off to ONLY show your custom slides below.')
                            ->default(true),

                        Forms\Components\Repeater::make('hero_carousel_slides')
                            ->label('Custom Carousel Slides')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->label('Slide Image')
                                    ->disk('public')
                                    ->directory('hero')
                                    ->image()
                                    ->imageEditor()
                                    ->required()
                                    ->columnSpanFull(),
                                Forms\Components\TextInput::make('tag')
                                    ->label('Top Tag')
                                    ->placeholder('e.g. Featured Drop')
                                    ->required(),
                                Forms\Components\TextInput::make('title')
                                    ->label('Main Title')
                                    ->placeholder('e.g. Exclusive Collection')
                                    ->required(),
                                Forms\Components\TextInput::make('badge')
                                    ->label('Corner Badge')
                                    ->placeholder('e.g. New In'),
                                Forms\Components\TextInput::make('link_url')
                                    ->label('Product / Destination Link')
                                    ->placeholder('e.g. /products/perfume or #')
                                    ->helperText('Where should this slide go when clicked?')
                                    ->url()
                                    ->nullable(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->addActionLabel('Add Custom Slide')
                            ->defaultItems(0),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            Setting::set($key, $value, 'hero_banner');
        }

        Notification::make()
            ->title('Hero Banner & Carousel Saved Successfully!')
            ->body('The storefront hero section has been updated in real time.')
            ->success()
            ->send();
    }
}
