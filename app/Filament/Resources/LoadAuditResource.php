<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoadAuditResource\Pages;
use App\Models\LoadAudit;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoadAuditResource extends Resource
{
    protected static ?string $model = LoadAudit::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Administración';

    protected static ?string $navigationLabel = 'Auditoría';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Registro de auditoría';

    protected static ?string $pluralModelLabel = 'Auditoría del sistema';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('module')
                    ->label('Módulo')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'portfolio'  => 'info',
                        'collection' => 'success',
                        default      => 'gray',
                    }),
                TextColumn::make('action')
                    ->label('Acción')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'completed'  => 'success',
                        'rejected', 'failed', 'cancelled' => 'danger',
                        'created'    => 'info',
                        default      => 'gray',
                    }),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->placeholder('—'),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(60)
                    ->tooltip(fn (LoadAudit $record): string => $record->description ?? ''),
                TextColumn::make('auditable_id')
                    ->label('ID Carga')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('module')
                    ->label('Módulo')
                    ->options(['portfolio' => 'Cartera', 'collection' => 'Recaudos']),
                SelectFilter::make('action')
                    ->label('Acción')
                    ->options([
                        'created'   => 'Creado',
                        'completed' => 'Completado',
                        'rejected'  => 'Rechazado',
                        'failed'    => 'Fallido',
                        'cancelled' => 'Anulado',
                    ]),
            ])
            ->paginated([25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoadAudits::route('/'),
        ];
    }
}
