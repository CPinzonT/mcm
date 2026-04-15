<?php

namespace App\Filament\Resources\BrandingProfileResource\Pages;

use App\Filament\Resources\BrandingProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBrandingProfile extends EditRecord
{
    protected static string $resource = BrandingProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
