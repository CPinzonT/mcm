<?php

namespace App\Filament\Widgets;

use App\Models\ManagementLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class FollowUpWidget extends BaseWidget
{
    protected static ?string $heading = 'Seguimientos Pendientes';
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ManagementLog::query()
                    ->where('status', '!=', 'closed')
                    ->whereNotNull('follow_up_date')
                    ->whereDate('follow_up_date', '<=', now()->addDays(7))
                    ->orderBy('follow_up_date')
            )
            ->columns([
                TextColumn::make('follow_up_date')
                    ->label('Seguimiento')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn ($state) => $state?->isPast() ? 'danger' : 'warning'),
                TextColumn::make('client.name')->label('Cliente')->searchable(),
                TextColumn::make('subject')->label('Asunto')->limit(50),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($s) => match($s) {
                        'call'      => 'Llamada',
                        'email'     => 'Correo',
                        'visit'     => 'Visita',
                        'agreement' => 'Acuerdo',
                        default     => 'Otro',
                    })
                    ->color('info'),
                TextColumn::make('promised_amount')->label('Monto Prometido')->money('COP'),
                TextColumn::make('promised_date')->label('Fecha Prometida')->date('d/m/Y'),
                TextColumn::make('advisor.name')->label('Asesor'),
            ])
            ->paginated([5, 10]);
    }
}
