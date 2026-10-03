<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Storefront & Content';

    protected static ?string $navigationLabel = 'Custom Pages & CMS';

    protected static ?string $title = 'Pages & Content Management';

    protected static ?int $navigationSort = 3;

    protected static function ensureSchemaExists(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('pages', 'is_full_width')) {
                \Illuminate\Support\Facades\Schema::table('pages', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('pages', 'is_full_width')) {
                        $table->boolean('is_full_width')->default(false)->after('content');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('pages', 'hide_header_hero')) {
                        $table->boolean('hide_header_hero')->default(false)->after('is_full_width');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('pages', 'custom_css')) {
                        $table->longText('custom_css')->nullable()->after('hide_header_hero');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('pages', 'custom_js')) {
                        $table->longText('custom_js')->nullable()->after('custom_css');
                    }
                });
            }
        } catch (\Throwable $e) {
            // Safe fallback: never crash if DB user has restrictive permissions
        }
    }

    public static function form(Form $form): Form
    {
        static::ensureSchemaExists();

        return $form
            ->schema([
                Forms\Components\Section::make('Page Header & Custom URL')
                    ->description('Set page title, permanent slug URL, and luxury hero badge.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Page Title')
                            ->placeholder('e.g. Women\'s Sharara Set, 100% Authenticity Guarantee')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug / Public URL Path')
                            ->placeholder('e.g. about-us or product-category/womens/sharara')
                            ->required()
                            ->unique(Page::class, 'slug', ignoreRecord: true)
                            ->prefix('/')
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn ($state) => trim($state, '/'))
                            ->helperText('Supports simple slugs (e.g. about-us) or multi-level prefix paths (e.g. product-category/womens/sharara).'),

                        Forms\Components\TextInput::make('hero_badge')
                            ->label('Hero Top Glowing Badge')
                            ->placeholder('e.g. ✨ Brand Heritage, 🛡️ 100% Authentic, 🚚 Nationwide Dispatch')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Hero Subtitle / Tagline')
                            ->placeholder('e.g. Curators of Authentic International Skincare & Couture Apparel')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Page Layout & Bespoke Canvas Mode')
                    ->description('Choose between standard editorial layout or a 100% full-width canvas for custom product listings and design.')
                    ->schema([
                        Forms\Components\Toggle::make('is_full_width')
                            ->label('Full-Width Custom Canvas')
                            ->helperText('Disables sidebar and typography constraints to give you 100% width for custom product grids, landing pages, and bespoke layouts.')
                            ->default(false)
                            ->live(),

                        Forms\Components\Toggle::make('hide_header_hero')
                            ->label('Hide Standard Dark Header Banner')
                            ->helperText('Completely removes the default top hero banner so you can design your page header from scratch.')
                            ->default(false)
                            ->visible(fn (Forms\Get $get) => (bool) $get('is_full_width')),
                    ])->columns(2),

                Forms\Components\Section::make('Page Content & Code Section')
                    ->description('Write rich text content, or switch to Raw Code mode for custom HTML product grids, styles, and scripts.')
                    ->schema([
                        Forms\Components\Toggle::make('use_custom_code')
                            ->label('Raw Code Editor Mode')
                            ->helperText('Toggle on to write raw HTML, Tailwind grids, product cards, and custom embeds directly.')
                            ->default(fn (?Page $record) => $record && ($record->is_full_width || !empty($record->custom_css) || !empty($record->custom_js)))
                            ->live(),

                        Forms\Components\Textarea::make('content')
                            ->label('Custom HTML & Product Listing Code')
                            ->visible(fn (Forms\Get $get) => (bool) $get('use_custom_code'))
                            ->dehydrated(fn (Forms\Get $get) => (bool) $get('use_custom_code'))
                            ->rows(20)
                            ->extraAttributes([
                                'class' => 'font-mono text-xs leading-relaxed',
                                'placeholder' => '<!-- Write custom HTML, Tailwind product grid cards, and links here -->'
                            ])
                            ->helperText('Tip: You can design custom product cards with Tailwind CSS, add images, links like /product/slug, and custom buttons.')
                            ->required(),

                        Forms\Components\RichEditor::make('content')
                            ->label('Page Body Content (Visual Editor)')
                            ->visible(fn (Forms\Get $get) => ! (bool) $get('use_custom_code'))
                            ->dehydrated(fn (Forms\Get $get) => ! (bool) $get('use_custom_code'))
                            ->toolbarButtons([
                                'attachFiles',
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ])
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Textarea::make('custom_css')
                                    ->label('Custom CSS (<style>)')
                                    ->placeholder("/* Custom styles for this page */\n.my-product-grid {\n  display: grid;\n}")
                                    ->rows(8)
                                    ->extraAttributes(['class' => 'font-mono text-xs'])
                                    ->helperText('Injected into a <style> block on this page.'),

                                Forms\Components\Textarea::make('custom_js')
                                    ->label('Custom JavaScript (<script>)')
                                    ->placeholder("// Custom JavaScript for this page\nconsole.log('Page ready');")
                                    ->rows(8)
                                    ->extraAttributes(['class' => 'font-mono text-xs'])
                                    ->helperText('Injected into a <script> block on this page.'),
                            ])
                            ->visible(fn (Forms\Get $get) => (bool) $get('use_custom_code') || (bool) $get('is_full_width')),
                    ]),

                Forms\Components\Section::make('Display Options & SEO Meta')
                    ->description('Control footer visibility, ordering, and search engine metadata.')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Page Active & Published')
                            ->default(true),

                        Forms\Components\Toggle::make('show_in_footer')
                            ->label('Show in Storefront Footer')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Display Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first in footer lists.'),

                        Forms\Components\TextInput::make('meta_title')
                            ->label('SEO Meta Title')
                            ->placeholder('e.g. 100% Authenticity Guarantee | Kazi Fashion World')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('SEO Meta Description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Page Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Page $record) => $record->subtitle),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Public URL')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => '/' . ltrim($state, '/'))
                    ->copyable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_full_width')
                    ->label('Full Width')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('show_in_footer')
                    ->label('In Footer')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\TernaryFilter::make('show_in_footer')
                    ->label('Footer Visibility'),
                Tables\Filters\TernaryFilter::make('is_full_width')
                    ->label('Full Width Canvas'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_live')
                    ->label('View Page')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Page $record) => url('/' . ltrim($record->slug, '/')))
                    ->openUrlInNewTab(),
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
            'index'  => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit'   => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
