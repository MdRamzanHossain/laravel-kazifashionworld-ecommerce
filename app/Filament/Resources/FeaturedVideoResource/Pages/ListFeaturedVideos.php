<?php

namespace App\Filament\Resources\FeaturedVideoResource\Pages;

use App\Filament\Resources\FeaturedVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeaturedVideos extends ListRecords
{
    protected static string $resource = FeaturedVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
