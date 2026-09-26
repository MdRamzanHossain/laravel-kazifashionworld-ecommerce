<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoReelResource\Pages;
use App\Models\VideoReel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VideoReelResource extends Resource
{
    protected static ?string $model = VideoReel::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';

    protected static ?string $navigationGroup = 'Storefront & Content';

    protected static ?string $navigationLabel = 'Shoppable Video Reels';

    protected static ?string $modelLabel = 'Shoppable Video Reel';

    protected static ?string $pluralModelLabel = 'Shoppable Video Reels';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Video Media & Visual Presentation')
                    ->description('Upload a 9:16 vertical video reel or provide an external MP4 streaming URL.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Reel Title / Internal Note')
                            ->placeholder('e.g. Korean Glass Skin Routine')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('overlay_heading')
                            ->label('In-Video Text Caption (Top Overlay)')
                            ->placeholder('e.g. Looking for KOREAN GLASS LIKE SKIN?')
                            ->helperText('Bold text displayed inside the video card overlay.')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('badge_text')
                            ->label('Top Glowing Badge')
                            ->placeholder('e.g. 🌸 Viral, Trending, 50% Off')
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('video_file')
                            ->label('Upload Vertical Video File (MP4, WebM)')
                            ->disk('public')
                            ->directory('reels')
                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime', 'video/ogg'])
                            ->maxSize(51200)
                            ->helperText('Upload a 9:16 vertical short video clip (up to 50MB).')
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('poster_image')
                            ->label('Custom Poster / Cover Image')
                            ->disk('public')
                            ->directory('reels')
                            ->image()
                            ->imageEditor()
                            ->helperText('High-res vertical thumbnail shown before playback.')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('video_url')
                            ->label('OR External Direct Video Stream URL (MP4 / BunnyCDN / Cloudinary)')
                            ->placeholder('https://assets.mixkit.co/videos/preview/mixkit-applying-skincare-cream-on-face-41484-large.mp4')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Shoppable Product Bar Attachment')
                    ->description('Link a store product to display the bottom product bar with image, price, and instant checkout.')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label('Select Store Product')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Selecting a product auto-syncs its price, thumbnail, category, and direct shopping route.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('custom_category_name')
                            ->label('Category Label Tag')
                            ->placeholder('e.g. Skin Care, Accessories, Make up')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('custom_product_name')
                            ->label('Custom Product Title Override')
                            ->placeholder('Leave empty to use attached product name')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('custom_price')
                            ->label('Offer / Display Price (BDT)')
                            ->numeric()
                            ->prefix('BDT')
                            ->placeholder('e.g. 1850')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('custom_original_price')
                            ->label('Regular / Strikethrough Price (BDT)')
                            ->numeric()
                            ->prefix('BDT')
                            ->placeholder('e.g. 2300')
                            ->helperText('Shown crossed-out next to offer price.')
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('custom_product_image')
                            ->label('Custom Product Thumbnail (Optional)')
                            ->disk('public')
                            ->directory('reels')
                            ->image()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('custom_product_url')
                            ->label('Custom Target Link (Optional)')
                            ->placeholder('e.g. /category/skincare or external link')
                            ->columnSpan(1),
                    ])->columns(2),

                Forms\Components\Section::make('Publishing & Display Position')
                    ->schema([
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort Order (Lower appears first)')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Published on Storefront')
                            ->default(true)
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('poster_image')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->height(64)
                    ->width(48)
                    ->extraImgAttributes(['class' => 'rounded-xl object-cover']),

                Tables\Columns\TextColumn::make('title')
                    ->label('Reel Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (VideoReel $record): string => $record->overlay_heading ? "Caption: {$record->overlay_heading}" : ''),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Attached Product')
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->default('Custom Product'),

                Tables\Columns\TextColumn::make('display_category')
                    ->label('Category')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('display_price')
                    ->label('Offer Price')
                    ->money('BDT')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVideoReels::route('/'),
            'create' => Pages\CreateVideoReel::route('/create'),
            'edit'   => Pages\EditVideoReel::route('/{record}/edit'),
        ];
    }
}