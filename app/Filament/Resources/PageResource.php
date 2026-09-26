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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Page Header & Identity')
                    ->description('Set page title, permanent slug URL, and luxury hero badge.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Page Title')
                            ->placeholder('e.g. 100% Authenticity Guarantee, About Us')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug URL Key')
                            ->placeholder('e.g. about-us')
                            ->required()
                            ->unique(Page::class, 'slug', ignoreRecord: true)
                            ->prefix('/')
                            ->maxLength(255)
                            ->helperText('This slug will form the public URL: www.site.com/your-slug'),

                        Forms\Components\TextInput::make('hero_badge')
                            ->label('Hero Top Glowing Badge')
                            ->placeholder('e.g. ✨ Brand Heritage, 🛡️ 100% Authentic, 🚚 Nationwide Dispatch')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Hero Subtitle / Tagline')
                            ->placeholder('e.g. Curators of Authentic International Skincare & Couture Apparel')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Page Content')
                    ->description('Rich luxury content, structured paragraphs, tables, or policy terms.')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Page Body Content')
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
                    ->formatStateUsing(fn ($state) => '/' . $state)
                    ->copyable()
                    ->sortable(),

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
            ])
            ->actions([
                Tables\Actions\Action::make('view_live')
                    ->label('View Page')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Page $record) => route('page.show', $record->slug))
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
