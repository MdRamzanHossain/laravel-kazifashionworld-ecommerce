<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageAnalyticsSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Tracking & Analytics';

    protected static ?string $title = 'Marketing & Analytics Setup';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.manage-analytics-settings'; // Reusing the same view to save time!

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'meta_pixel_id' => Setting::get('meta_pixel_id'),
            'ga4_measurement_id' => Setting::get('ga4_measurement_id'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Meta (Facebook) Pixel & Google Analytics')
                    ->description('Integrate Meta Pixel and Google Analytics 4 (GA4) for E-commerce tracking across the entire site.')
                    ->schema([
                        Forms\Components\TextInput::make('meta_pixel_id')
                            ->label('Meta Pixel ID')
                            ->placeholder('e.g. 123456789012345')
                            ->helperText('Enter your Facebook Pixel ID to track PageView, ViewContent, AddToCart, InitiateCheckout, and Purchase events.'),

                        Forms\Components\TextInput::make('ga4_measurement_id')
                            ->label('GA4 Measurement ID')
                            ->placeholder('e.g. G-XXXXXXX')
                            ->helperText('Enter your GA4 Measurement ID to track e-commerce events.'),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            Setting::set($key, $value, 'analytics');
        }

        Notification::make()
            ->title('Analytics settings saved successfully!')
            ->success()
            ->send();
    }
}