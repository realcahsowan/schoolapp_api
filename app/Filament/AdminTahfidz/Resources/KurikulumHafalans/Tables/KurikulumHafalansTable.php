<?php

namespace App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class KurikulumHafalansTable
{
    public static function configure(Table $table): Table
    {
        $tahunAjaran = app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
        $semester = app(\App\Settings\GeneralSettings::class)->semester;

        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('tahun_ajaran')->label('Tahun Ajaran'),
                \Filament\Tables\Columns\TextColumn::make('semester')->label('Semester'),
                \Filament\Tables\Columns\TextColumn::make('grade')->label('Grade'),
                \Filament\Tables\Columns\TextColumn::make('total_juz_hafalan_baru')->label('Total Juz Hafalan Baru'),
                \Filament\Tables\Columns\TextColumn::make('total_juz_hafalan_murojaah')->label('Total Juz Hafalan Murojaah'),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->options(
                        fn() => \App\Models\Tahfidz\KurikulumHafalan::query()
                        ->orderByDesc('tahun_ajaran')
                        ->pluck('tahun_ajaran', 'tahun_ajaran')
                        ->unique()
                        ->toArray()
                    )->default($tahunAjaran),
                \Filament\Tables\Filters\SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([
                        '1' => 'Ganjil',
                        '2' => 'Genap',
                    ])->default($semester),
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
