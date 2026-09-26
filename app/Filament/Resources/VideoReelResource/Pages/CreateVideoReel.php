<?php

namespace App\Filament\Resources\VideoReelResource\Pages;

use App\Filament\Resources\VideoReelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVideoReel extends CreateRecord
{
    protected static string $resource = VideoReelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}