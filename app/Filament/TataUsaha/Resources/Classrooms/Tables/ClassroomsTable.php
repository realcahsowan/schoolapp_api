<?php

namespace App\Filament\TataUsaha\Resources\Classrooms\Tables;

use App\Settings\GeneralSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClassroomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->label('Nama Kelas')->searchable(),
                // TextColumn::make('alias')->label('Alias'),
                // TextColumn::make('level')->label('Tingkat')->searchable(),
                // TextColumn::make('rombel')->label('Rombel')->searchable(),
                // TextColumn::make('jurusan_id')->label('Jurusan')->numeric()->sortable(),
                // TextColumn::make('tingkat_id')->label('Tingkat')->numeric()->sortable(),
                // TextColumn::make('kurikulum_id')->label('Kurikulum')->numeric()->sortable(),
                TextColumn::make('tahun_ajaran')->label('Tahun Ajaran')->searchable(),
                // IconColumn::make('is_promoted')->label('Naik Kelas')->boolean(),
                TextColumn::make('employee.nama')->label('Wali Kelas')
                    ->sortable(),
                // TextColumn::make('school_id')->label('Sekolah')->numeric()->sortable(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Diubah')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->options(fn() => array_combine(
                        (app(GeneralSettings::class)->years ?? []),
                        (app(GeneralSettings::class)->years ?? [])
                    ))
                    ->default(app(GeneralSettings::class)->tahun_ajaran ?? null)
                    ->searchable()
                    ->placeholder('Semua Tahun'),
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
