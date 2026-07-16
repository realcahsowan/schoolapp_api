<?php

namespace App\Filament\Resources\Schools\Tables;

use App\Traits\SekolahTrait;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

class SchoolsTable
{
    use SekolahTrait;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Sekolah')
                    ->searchable(),
                TextColumn::make('nsm')
                    ->label('NSM')
                    ->searchable(),
                TextColumn::make('npsn')
                    ->label('NPSN')
                    ->searchable(),
                TextColumn::make('jenjang')
                    ->label('Jenjang')
                    ->formatStateUsing(fn ($state) => static::getJenjang($state)),
                TextColumn::make('telepon')
                    ->label('Telepon')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getJenjang($jenjang): string
    {
        return Arr::get((new class () {
            use SekolahTrait;
        })->getJenjangOptions(), $jenjang, '');
    }
}
