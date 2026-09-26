<?php

namespace App\Filament\Resources\FeaturedVideoResource\Pages;

use App\Filament\Resources\FeaturedVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFeaturedVideo extends CreateRecord
{
    protected static string $resource = FeaturedVideoResource::class;
}
