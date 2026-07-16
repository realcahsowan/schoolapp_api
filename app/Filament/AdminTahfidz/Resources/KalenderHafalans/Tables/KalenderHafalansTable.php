<?php

namespace App\Filament\AdminTahfidz\Resources\KalenderHafalans\Tables;

use App\Models\Tahfidz\KalenderHafalan;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KalenderHafalansTable
{
    public static function configure(Table $table): Table
    {
        $tahunAjaran = app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
        $semester = app(\App\Settings\GeneralSettings::class)->semester;
        return $table
            ->modifyQueryUsing(function ($query) use ($tahunAjaran) {
                $query->where('tahun_ajaran', $tahunAjaran);
            })
            ->columns([
                TextColumn::make('month')
                    ->state(function ($record) {
                        return Carbon::parse($record->tanggal)->locale('id')->translatedFormat('F');
                    })
                    ->sortable(),
                TextColumn::make('week')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('day')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                // IconColumn::make('is_hp_only')
                //     ->boolean(),
                IconColumn::make('is_weekly_examination')
                    ->label('Ujian Pekanan')
                    ->boolean(),
                // IconColumn::make('is_disabled')
                //     ->boolean(),
                IconColumn::make('is_hp_only')
                    ->label('Hafalan Pagi Saja')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                SelectFilter::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->options(fn() => KalenderHafalan::query()
                        ->distinct('tahun_ajaran')
                        ->pluck('tahun_ajaran', 'tahun_ajaran')
                        ->toArray())
                    ->default($tahunAjaran),
                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([
                        1 => '1',
                        2 => '2',
                    ])
                    ->default($semester),

                SelectFilter::make('month')
                    ->label('Bulan')
                    ->options([
                        '1' => 'Januari',
                        '2' => 'Februari',
                        '3' => 'Maret',
                        '4' => 'April',
                        '5' => 'Mei',
                        '6' => 'Juni',
                        '7' => 'Juli',
                        '8' => 'Agustus',
                        '9' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->whereMonth('tanggal', $data['value']);
                        }
                    }),
                SelectFilter::make('is_hp_only')
                    ->label('Hafalan Pagi Saja')
                    ->options([
                        '' => 'Semua',
                        1 => 'Ya',
                        0 => 'Tidak',
                    ])
                    ->query(function ($query, $data) {
                        if (!is_null($data['value']) && $data['value'] !== '') {
                            $query->where('is_hp_only', $data['value']);
                        }
                    }),
                // ...existing code...
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
