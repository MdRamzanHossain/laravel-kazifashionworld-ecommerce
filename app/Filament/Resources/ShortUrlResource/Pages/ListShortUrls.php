<?php

namespace App\Filament\Resources\ShortUrlResource\Pages;

use App\Filament\Resources\ShortUrlResource;
use App\Filament\Widgets\ShortUrlStatsWidget;
use Filament\Resources\Pages\ListRecords;

class ListShortUrls extends ListRecords
{
    protected static string $resource = ShortUrlResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            ShortUrlStatsWidget::class,
        ];
    }
}
