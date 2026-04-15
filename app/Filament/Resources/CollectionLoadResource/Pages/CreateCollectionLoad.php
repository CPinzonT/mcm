<?php

namespace App\Filament\Resources\CollectionLoadResource\Pages;

use App\Filament\Resources\CollectionLoadResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateCollectionLoad extends CreateRecord
{
    protected static string $resource = CollectionLoadResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reference']   = 'RC-' . strtoupper(Str::random(8));
        $data['uploaded_by'] = auth()->id();
        $data['status']      = 'pending';

        return $data;
    }
}
