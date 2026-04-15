<?php

namespace App\Filament\Resources\PortfolioLoadResource\Pages;

use App\Filament\Resources\PortfolioLoadResource;
use App\Models\PortfolioLoad;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreatePortfolioLoad extends CreateRecord
{
    protected static string $resource = PortfolioLoadResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['reference']   = 'CL-' . strtoupper(Str::random(8));
        $data['uploaded_by'] = auth()->id();
        $data['status']      = 'pending';

        return $data;
    }
}
