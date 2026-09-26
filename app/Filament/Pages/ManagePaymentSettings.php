<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\PaymentSettingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManagePaymentSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Settings & Setup';

    protected static ?string $navigationLabel = 'Payment Gateways & Accounts';

    protected static ?string $title = 'Payment Gateways & Merchant Accounts Setup';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.manage-payment-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $bkash = PaymentSettingService::getBkashConfig();
        $nagad = PaymentSettingService::getNagadConfig();
        $ssl = PaymentSettingService::getSslCommerzConfig();
        $cod = PaymentSettingService::getCodConfig();
        $bank = PaymentSettingService::getBankTransferConfig();

        $this->form->fill([
            // bKash
            'payment_bkash_enabled'        => $bkash['enabled'],
            'payment_bkash_type'           => $bkash['type'],
            'payment_bkash_sandbox'        => $bkash['sandbox_mode'],
            'payment_bkash_wallet_number'  => $bkash['wallet_number'],
            'payment_bkash_username'       => $bkash['username'],
            'payment_bkash_password'       => $bkash['password'],
            'payment_bkash_app_key'        => $bkash['app_key'],
            'payment_bkash_app_secret'     => $bkash['app_secret'],
            'payment_bkash_instructions'   => $bkash['instructions'],

            // Nagad
            'payment_nagad_enabled'        => $nagad['enabled'],
            'payment_nagad_sandbox'        => $nagad['sandbox_mode'],
            'payment_nagad_wallet_number'  => $nagad['wallet_number'],
            'payment_nagad_merchant_id'    => $nagad['merchant_id'],
            'payment_nagad_public_key'     => $nagad['public_key'],
            'payment_nagad_private_key'    => $nagad['private_key'],
            'payment_nagad_instructions'   => $nagad['instructions'],

            // SSLCommerz
            'payment_sslcommerz_enabled'        => $ssl['enabled'],
            'payment_sslcommerz_sandbox'        => $ssl['sandbox_mode'],
            'payment_sslcommerz_store_id'       => $ssl['store_id'],
            'payment_sslcommerz_store_password' => $ssl['store_password'],
            'payment_sslcommerz_instructions'   => $ssl['instructions'],

            // Cash on Delivery
            'payment_cod_enabled'          => $cod['enabled'],
            'payment_cod_extra_fee'        => $cod['extra_fee'],
            'payment_cod_instructions'     => $cod['instructions'],

            // Bank Transfer
            'payment_bank_enabled'         => $bank['enabled'],
            'payment_bank_name'            => $bank['bank_name'],
            'payment_bank_account_name'    => $bank['account_name'],
            'payment_bank_account_number'  => $bank['account_number'],
            'payment_bank_branch'          => $bank['branch_name'],
            'payment_bank_routing'         => $bank['routing_number'],
            'payment_bank_instructions'    => $bank['instructions'],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. bKash Section
                Forms\Components\Section::make('bKash Payment Gateway & Merchant Account')
                    ->description('Configure bKash Tokenized Direct API or Merchant Wallet details for instant checkout.')
                    ->schema([
                        Forms\Components\Toggle::make('payment_bkash_enabled')
                            ->label('Enable bKash Payment Method')
                            ->default(true),

                        Forms\Components\Select::make('payment_bkash_type')
                            ->label('Integration Type')
                            ->options([
                                'tokenized' => 'bKash Tokenized Direct API (Automated Instant Verification)',
                                'manual'    => 'bKash Merchant / Personal Send Money (Manual TrxID Verification)',
                            ])
                            ->default('tokenized')
                            ->required()
                            ->native(false),

                        Forms\Components\Toggle::make('payment_bkash_sandbox')
                            ->label('Sandbox / Test Mode')
                            ->default(true),

                        Forms\Components\TextInput::make('payment_bkash_wallet_number')
                            ->label('bKash Merchant / Agent / Personal Number')
                            ->placeholder('e.g. 01735940279')
                            ->required(),

                        Forms\Components\TextInput::make('payment_bkash_username')
                            ->label('bKash API Username')
                            ->placeholder('sandbox_username')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('payment_bkash_password')
                            ->label('bKash API Password')
                            ->password()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('payment_bkash_app_key')
                            ->label('bKash App Key')
                            ->placeholder('app_key_here')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('payment_bkash_app_secret')
                            ->label('bKash App Secret')
                            ->password()
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('payment_bkash_instructions')
                            ->label('Customer Checkout Instructions')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                // 2. SSLCommerz Section (Cards & Internet Banking)
                Forms\Components\Section::make('SSLCommerz Gateway (Cards & Internet Banking)')
                    ->description('Accept Visa, Mastercard, AMEX, UnionPay, and Internet Banking accounts.')
                    ->schema([
                        Forms\Components\Toggle::make('payment_sslcommerz_enabled')
                            ->label('Enable SSLCommerz / Online Cards')
                            ->default(true),

                        Forms\Components\Toggle::make('payment_sslcommerz_sandbox')
                            ->label('Sandbox / Test Mode')
                            ->default(true),

                        Forms\Components\TextInput::make('payment_sslcommerz_store_id')
                            ->label('SSLCommerz Store ID')
                            ->placeholder('testbox')
                            ->required(),

                        Forms\Components\TextInput::make('payment_sslcommerz_store_password')
                            ->label('SSLCommerz Store Password')
                            ->password()
                            ->required(),

                        Forms\Components\Textarea::make('payment_sslcommerz_instructions')
                            ->label('Customer Checkout Instructions')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                // 3. Nagad Section
                Forms\Components\Section::make('Nagad Payment Gateway & Account')
                    ->description('Accept direct Nagad wallet and mobile app payments.')
                    ->schema([
                        Forms\Components\Toggle::make('payment_nagad_enabled')
                            ->label('Enable Nagad Payment Method')
                            ->default(false),

                        Forms\Components\Toggle::make('payment_nagad_sandbox')
                            ->label('Sandbox / Test Mode')
                            ->default(true),

                        Forms\Components\TextInput::make('payment_nagad_wallet_number')
                            ->label('Nagad Merchant / Personal Number')
                            ->placeholder('e.g. 01735940279'),

                        Forms\Components\TextInput::make('payment_nagad_merchant_id')
                            ->label('Nagad Merchant ID')
                            ->placeholder('NAGAD_MERCHANT_ID'),

                        Forms\Components\Textarea::make('payment_nagad_public_key')
                            ->label('Nagad Public Key')
                            ->rows(2),

                        Forms\Components\Textarea::make('payment_nagad_private_key')
                            ->label('Nagad Private Key')
                            ->rows(2),

                        Forms\Components\Textarea::make('payment_nagad_instructions')
                            ->label('Customer Checkout Instructions')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2)->collapsed(),

                // 4. Cash on Delivery (COD) Section
                Forms\Components\Section::make('Cash on Delivery (COD)')
                    ->description('Configure Cash on Delivery options, fees, and instructions.')
                    ->schema([
                        Forms\Components\Toggle::make('payment_cod_enabled')
                            ->label('Enable Cash on Delivery (COD)')
                            ->default(true),

                        Forms\Components\TextInput::make('payment_cod_extra_fee')
                            ->label('Extra COD Handling Charge (BDT)')
                            ->numeric()
                            ->default(0.00)
                            ->prefix('BDT')
                            ->helperText('Optional handling fee added when customer chooses COD.'),

                        Forms\Components\Textarea::make('payment_cod_instructions')
                            ->label('Customer Checkout Instructions')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                // 5. Bank Wire Transfer & Rocket Section
                Forms\Components\Section::make('Corporate Bank Wire Transfer')
                    ->description('Allow customers to deposit directly into corporate bank accounts.')
                    ->schema([
                        Forms\Components\Toggle::make('payment_bank_enabled')
                            ->label('Enable Bank Transfer Method')
                            ->default(false),

                        Forms\Components\TextInput::make('payment_bank_name')
                            ->label('Bank Name')
                            ->placeholder('e.g. City Bank / BRAC Bank'),

                        Forms\Components\TextInput::make('payment_bank_account_name')
                            ->label('Account Name')
                            ->placeholder('Kazi Fashion World Ltd.'),

                        Forms\Components\TextInput::make('payment_bank_account_number')
                            ->label('Account Number')
                            ->placeholder('1234567890'),

                        Forms\Components\TextInput::make('payment_bank_branch')
                            ->label('Branch Name')
                            ->placeholder('Gulshan Branch, Dhaka'),

                        Forms\Components\TextInput::make('payment_bank_routing')
                            ->label('Routing Number')
                            ->placeholder('225271234'),

                        Forms\Components\Textarea::make('payment_bank_instructions')
                            ->label('Customer Checkout Instructions')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2)->collapsed(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            Setting::set($key, $value, 'payment_gateways');
        }

        Notification::make()
            ->title('Payment Setup Saved Successfully!')
            ->body('Merchant account credentials and gateway configurations have been updated.')
            ->success()
            ->send();
    }
}