<?php

namespace App\Filament\Resources\CastigoCaseResource\Pages;

use App\Filament\Resources\CastigoCaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCastigoCase extends CreateRecord
{
    protected static string $resource = CastigoCaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}
