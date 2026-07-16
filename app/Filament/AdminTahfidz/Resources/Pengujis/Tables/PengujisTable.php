<?php

namespace App\Filament\AdminTahfidz\Resources\Pengujis\Tables;

use App\Settings\GeneralSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengujisTable
{
    public static function configure(Table $table): Table
    {
        $semester = app(GeneralSettings::class)->semester;

        return $table
            ->columns([
                // TextColumn::make('school_id')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('employee_id')
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('nama')
                    ->searchable(),
                TextColumn::make('tahun_ajaran')
                    ->searchable(),
                TextColumn::make('semester')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gender')
                    ->badge()
                    ->color(fn($state) => $state === 'male' ? 'info' : 'danger'),
                TextColumn::make('total_students')
                    ->numeric()
                    ->sortable(),
                // TextColumn::make('percentage')
                //     ->numeric()
                //     ->sortable(),
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
                SelectFilter::make('tahun_ajaran')
                    ->options(fn() => \App\Models\Tahfidz\Penguji::distinct()->pluck('tahun_ajaran', 'tahun_ajaran')->toArray()),
                SelectFilter::make('semester')
                    ->options([
                        1 => 'Ganjil',
                        2 => 'Genap',
                    ])
                    ->default($semester),
                SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ]),
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
}
