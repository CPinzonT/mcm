<?php

namespace App\Filament\Resources\BrandingProfileResource\Pages;

use App\Filament\Resources\BrandingProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBrandingProfiles extends ListRecords
{
    protected static string $resource = BrandingProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
