<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\SmsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

class ManageSmsSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Settings & Setup';

    protected static ?string $navigationLabel = 'SMS Gateway & Automation';

    protected static ?string $title = 'SMS Gateway Accounts & Automated Alerts';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.manage-sms-settings';

    public ?array $data = [];

    public string $testRecipient = '';
    public string $testMessage = 'Test SMS: Your Kazi Fashion World SMS Gateway is configured successfully!';

    public function mount(): void
    {
        $this->form->fill([
            // Gateway Provider
            'sms_provider'                  => Setting::get('sms_provider', 'greenweb'),
            'sms_api_key'                   => Setting::get('sms_api_key', config('services.local_sms.api_key', '')),
            'sms_sender_id'                 => Setting::get('sms_sender_id', ''),
            'sms_client_id'                 => Setting::get('sms_client_id', ''),
            'sms_custom_url'                => Setting::get('sms_custom_url', config('services.local_sms.url', '')),
            'sms_custom_method'             => Setting::get('sms_custom_method', 'GET'),

            // Order Confirmation Trigger & Template
            'sms_trigger_order_placed'      => (bool) filter_var(Setting::get('sms_trigger_order_placed', true), FILTER_VALIDATE_BOOLEAN),
            'sms_template_order_placed'     => Setting::get('sms_template_order_placed', 'Dear {customer_name}, your order #{order_number} of BDT {amount} has been placed successfully! Track your order: {tracking_url}'),

            // Order Shipped Trigger & Template
            'sms_trigger_order_shipped'     => (bool) filter_var(Setting::get('sms_trigger_order_shipped', true), FILTER_VALIDATE_BOOLEAN),
            'sms_template_order_shipped'    => Setting::get('sms_template_order_shipped', 'Dear {customer_name}, your order #{order_number} has been handed over to {courier_name} (Consignment #{consignment_id}). Track: {tracking_url}'),

            // Order Delivered Trigger & Template
            'sms_trigger_order_delivered'   => (bool) filter_var(Setting::get('sms_trigger_order_delivered', true), FILTER_VALIDATE_BOOLEAN),
            'sms_template_order_delivered'  => Setting::get('sms_template_order_delivered', 'Dear {customer_name}, your order #{order_number} has been delivered successfully! Thank you for shopping with {store_name}.'),

            // Order Cancelled Trigger & Template
            'sms_trigger_order_cancelled'   => (bool) filter_var(Setting::get('sms_trigger_order_cancelled', true), FILTER_VALIDATE_BOOLEAN),
            'sms_template_order_cancelled'  => Setting::get('sms_template_order_cancelled', 'Dear {customer_name}, your order #{order_number} has been cancelled. If you have any inquiries, please contact our customer care.'),

            // Admin Alert Trigger & Template
            'sms_trigger_admin_alert'       => (bool) filter_var(Setting::get('sms_trigger_admin_alert', true), FILTER_VALIDATE_BOOLEAN),
            'sms_admin_phone'               => Setting::get('sms_admin_phone', '01735940279'),
            'sms_template_admin_alert'      => Setting::get('sms_template_admin_alert', '[New Order Alert] #{order_number} received from {customer_name} ({customer_phone}) for BDT {amount}.'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. Gateway API Settings
                Forms\Components\Section::make('SMS Gateway API Provider')
                    ->description('Select your SMS provider and provide your API token / sender ID.')
                    ->schema([
                        Forms\Components\Select::make('sms_provider')
                            ->label('Select Active SMS Provider')
                            ->options([
                                'greenweb'   => 'Greenweb SMS BD (Popular in Bangladesh)',
                                'bulksmsbd'  => 'BulkSMSBD (Masking / Non-Masking)',
                                'mim_sms'    => 'MiM SMS API (High Speed BD Gateway)',
                                'elitbuzz'   => 'ElitBuzz Technologies',
                                'twilio'     => 'Twilio (International SMS)',
                                'custom_api' => 'Custom HTTP GET / POST SMS API',
                            ])
                            ->live()
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('sms_sender_id')
                            ->label('Approved Sender ID / Masking (Optional)')
                            ->placeholder('e.g. 8809612... or BrandName')
                            ->helperText('Your approved masking sender ID from BTRC/Provider.'),

                        Forms\Components\TextInput::make('sms_api_key')
                            ->label('API Key / Secret Token')
                            ->password()
                            ->placeholder('Enter SMS API Token')
                            ->required(),

                        Forms\Components\TextInput::make('sms_client_id')
                            ->label('Client ID / Username (If required)')
                            ->placeholder('e.g. username'),

                        Forms\Components\TextInput::make('sms_custom_url')
                            ->label('Custom API Endpoint URL')
                            ->placeholder('https://api.yourprovider.com/sms/send')
                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom_api')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('sms_custom_method')
                            ->label('HTTP Request Method')
                            ->options([
                                'GET'  => 'HTTP GET',
                                'POST' => 'HTTP POST',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('sms_provider') === 'custom_api')
                            ->default('GET')
                            ->native(false),
                    ])->columns(2),

                // 2. Automated Event Triggers & Templates
                Forms\Components\Section::make('Automated Customer SMS Triggers & Message Templates')
                    ->description('Available Placeholders: {customer_name}, {order_number}, {amount}, {tracking_url}, {courier_name}, {consignment_id}, {store_name}')
                    ->schema([
                        // Order Placed
                        Forms\Components\Fieldset::make('1. Order Confirmation (Instant on Checkout)')
                            ->schema([
                                Forms\Components\Toggle::make('sms_trigger_order_placed')
                                    ->label('Send SMS on Order Placement')
                                    ->default(true),

                                Forms\Components\Textarea::make('sms_template_order_placed')
                                    ->label('Message Template')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->required(),
                            ]),

                        // Order Shipped
                        Forms\Components\Fieldset::make('2. Order Dispatched / Shipped')
                            ->schema([
                                Forms\Components\Toggle::make('sms_trigger_order_shipped')
                                    ->label('Send SMS when Dispatched to Courier')
                                    ->default(true),

                                Forms\Components\Textarea::make('sms_template_order_shipped')
                                    ->label('Message Template')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->required(),
                            ]),

                        // Order Delivered
                        Forms\Components\Fieldset::make('3. Order Successfully Delivered')
                            ->schema([
                                Forms\Components\Toggle::make('sms_trigger_order_delivered')
                                    ->label('Send SMS when Parcel is Delivered')
                                    ->default(true),

                                Forms\Components\Textarea::make('sms_template_order_delivered')
                                    ->label('Message Template')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->required(),
                            ]),

                        // Order Cancelled
                        Forms\Components\Fieldset::make('4. Order Cancelled')
                            ->schema([
                                Forms\Components\Toggle::make('sms_trigger_order_cancelled')
                                    ->label('Send SMS when Order is Cancelled')
                                    ->default(true),

                                Forms\Components\Textarea::make('sms_template_order_cancelled')
                                    ->label('Message Template')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->required(),
                            ]),
                    ]),

                // 3. Admin Instant Alert
                Forms\Components\Section::make('Admin Real-Time New Order SMS Alert')
                    ->description('Receive an immediate SMS on your admin mobile phone whenever a customer places an order.')
                    ->schema([
                        Forms\Components\Toggle::make('sms_trigger_admin_alert')
                            ->label('Send New Order Alert SMS to Admin Phone')
                            ->default(true),

                        Forms\Components\TextInput::make('sms_admin_phone')
                            ->label('Admin Alert Mobile Phone Number')
                            ->placeholder('e.g. 01735940279')
                            ->required(),

                        Forms\Components\Textarea::make('sms_template_admin_alert')
                            ->label('Admin Alert Template')
                            ->rows(2)
                            ->columnSpanFull()
                            ->required(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            Setting::set($key, $value, 'sms_gateway');
        }

        Notification::make()
            ->title('SMS Gateway Settings Saved!')
            ->body('SMS provider credentials and automated message templates have been updated.')
            ->success()
            ->send();
    }

    public function sendTestSms(): void
    {
        if (empty($this->testRecipient)) {
            Notification::make()
                ->title('Missing Test Phone Number')
                ->body('Please enter a recipient mobile number to send the test SMS.')
                ->warning()
                ->send();
            return;
        }

        try {
            $result = SmsService::sendTestSms($this->testRecipient, $this->testMessage);

            Notification::make()
                ->title('Test SMS Sent Successfully!')
                ->body('Provider Response: ' . json_encode($result))
                ->success()
                ->send();
        } catch (Throwable $e) {
            Notification::make()
                ->title('Test SMS Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}