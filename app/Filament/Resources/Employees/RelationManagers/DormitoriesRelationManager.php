<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class DormitoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'dormitories';

    protected static ?string $relatedResource = EmployeeResource::class;

    protected static ?string $title = 'Asrama';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nama Asrama')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('room')
                    ->label('Nomor Kamar')
                    ->sortable(),
                \Filament\Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
