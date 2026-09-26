<?php

namespace App\Filament\Resources\VideoReelResource\Pages;

use App\Filament\Resources\VideoReelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVideoReels extends ListRecords
{
    protected static string $resource = VideoReelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add New Video Reel'),
        ];
    }
}