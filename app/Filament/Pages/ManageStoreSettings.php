<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\SettingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageStoreSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Sales & Discounts';

    protected static ?string $navigationLabel = 'Automatic Discounts & Delivery';

    protected static ?string $title = 'Automatic Spending Discounts & Delivery Settings';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.manage-store-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // Free Shipping Threshold
            'free_shipping_enabled'          => (bool) filter_var(Setting::get('free_shipping_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'free_shipping_min_spend'        => SettingService::getFreeShippingThreshold(),
            'free_shipping_scope'            => SettingService::getFreeShippingScope(),

            // Delivery Rates
            'inside_dhaka_fee'               => Setting::get('inside_dhaka_fee', 80.00),
            'outside_dhaka_fee'              => Setting::get('outside_dhaka_fee', 150.00),

            // Automatic Cart Spending Tier Discount
            'auto_discount_enabled'          => (bool) filter_var(Setting::get('auto_discount_enabled', false), FILTER_VALIDATE_BOOLEAN),
            'auto_discount_name'             => Setting::get('auto_discount_name', 'VIP Spend Bonus'),
            'auto_discount_min_spend'        => Setting::get('auto_discount_min_spend', 5000.00),
            'auto_discount_type'             => Setting::get('auto_discount_type', 'fixed'),
            'auto_discount_value'            => Setting::get('auto_discount_value', 300.00),

            // Announcement Bar
            'announcement_bar_enabled'       => (bool) filter_var(Setting::get('announcement_bar_enabled', true), FILTER_VALIDATE_BOOLEAN),
            'announcement_bar_text'          => SettingService::getAnnouncementText(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Automatic Free Delivery Spending Threshold')
                    ->description('Automatically eliminate delivery fees when customer order value meets or exceeds the minimum spend.')
                    ->schema([
                        Forms\Components\Toggle::make('free_shipping_enabled')
                            ->label('Enable Automatic Free Shipping')
                            ->default(true),

                        Forms\Components\TextInput::make('free_shipping_min_spend')
                            ->label('Minimum Cart Subtotal (BDT)')
                            ->numeric()
                            ->required()
                            ->prefix('BDT')
                            ->helperText('Customers spending this amount or more will automatically receive free delivery at checkout.'),

                        Forms\Components\Select::make('free_shipping_scope')
                            ->label('Applicable Delivery Region')
                            ->options([
                                'inside_dhaka' => 'Inside Dhaka Orders Only',
                                'all'          => 'All Nationwide Orders (Inside & Outside Dhaka)',
                            ])
                            ->default('inside_dhaka')
                            ->required()
                            ->native(false),
                    ])->columns(3),

                Forms\Components\Section::make('Standard Base Delivery Rates')
                    ->description('Base delivery fees applied when free shipping threshold is not met.')
                    ->schema([
                        Forms\Components\TextInput::make('inside_dhaka_fee')
                            ->label('Inside Dhaka Standard Fee (BDT)')
                            ->numeric()
                            ->required()
                            ->prefix('BDT'),

                        Forms\Components\TextInput::make('outside_dhaka_fee')
                            ->label('Outside Dhaka Standard Fee (BDT)')
                            ->numeric()
                            ->required()
                            ->prefix('BDT'),
                    ])->columns(2),

                Forms\Components\Section::make('Automatic Cart Tier Spending Discount (No Promo Code Required)')
                    ->description('Reward high-value carts with an automatic cash discount during checkout.')
                    ->schema([
                        Forms\Components\Toggle::make('auto_discount_enabled')
                            ->label('Enable Automatic Spending Tier Discount')
                            ->default(false),

                        Forms\Components\TextInput::make('auto_discount_name')
                            ->label('Promotion Display Name')
                            ->placeholder('e.g. VIP Spend Bonus')
                            ->required(),

                        Forms\Components\TextInput::make('auto_discount_min_spend')
                            ->label('Minimum Cart Spend (BDT)')
                            ->numeric()
                            ->prefix('BDT')
                            ->required(),

                        Forms\Components\Select::make('auto_discount_type')
                            ->label('Discount Type')
                            ->options([
                                'fixed'      => 'Fixed Amount (BDT)',
                                'percentage' => 'Percentage (%)',
                            ])
                            ->default('fixed')
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('auto_discount_value')
                            ->label('Discount Amount / %')
                            ->numeric()
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Storefront Announcement Banner')
                    ->description('Customize the announcement banner shown at the top of every page.')
                    ->schema([
                        Forms\Components\Toggle::make('announcement_bar_enabled')
                            ->label('Show Top Announcement Bar')
                            ->default(true),

                        Forms\Components\TextInput::make('announcement_bar_text')
                            ->label('Banner Message Text')
                            ->columnSpanFull()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Loyalty Points & Rewards Program')
                    ->description('Configure how customers earn and redeem loyalty points.')
                    ->schema([
                        Forms\Components\Toggle::make('loyalty_points_enabled')
                            ->label('Enable Loyalty Points Program')
                            ->default(true)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('loyalty_points_per_spend')
                            ->label('Points Earned Per 100 BDT Spent')
                            ->numeric()
                            ->default(1)
                            ->helperText('e.g. 1 point for every 100 BDT spent.')
                            ->required(),

                        Forms\Components\TextInput::make('loyalty_points_per_review')
                            ->label('Points Earned Per Approved Review')
                            ->numeric()
                            ->default(50)
                            ->helperText('Reward points for verified product reviews.')
                            ->required(),

                        Forms\Components\TextInput::make('loyalty_points_redemption_value')
                            ->label('Redemption Value (BDT per Point)')
                            ->numeric()
                            ->step(0.01)
                            ->default(1.00)
                            ->helperText('e.g. 1 point = 1 BDT discount.')
                            ->required(),
                    ])->columns(3),

                
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            Setting::set($key, $value, 'discount_and_delivery');
        }

        Notification::make()
            ->title('Store settings saved successfully!')
            ->body('Automatic spending discounts and delivery threshold rules have been updated.')
            ->success()
            ->send();
    }
}
