<?php

namespace App\Filament\Resources\VideoReelResource\Pages;

use App\Filament\Resources\VideoReelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVideoReel extends EditRecord
{
    protected static string $resource = VideoReelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}