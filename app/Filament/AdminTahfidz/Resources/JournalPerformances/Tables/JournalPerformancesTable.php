<?php

namespace App\Filament\AdminTahfidz\Resources\JournalPerformances\Tables;

use App\Filament\AdminTahfidz\Resources\Murobbis\Pages\ViewMurobbi;
use App\Settings\GeneralSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class JournalPerformancesTable
{
    public static function configure(Table $table): Table
    {
        $general = app(GeneralSettings::class);
        $defaultTahun = $general?->tahun_ajaran;
        $defaultSemester = $general?->semester;

        return $table
            ->defaultSort('awal', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('murobbi.nama')->label('Murobbi')->searchable(),
                Tables\Columns\TextColumn::make('awal')->label('Awal Periode')->date('d-m-Y'),
                Tables\Columns\TextColumn::make('akhir')->label('Akhir Periode')->date('d-m-Y'),
                Tables\Columns\TextColumn::make('total_hari')->label('Total Hari'),
                Tables\Columns\TextColumn::make('target')->label('Target'),
                Tables\Columns\TextColumn::make('realisasi')->label('Realisasi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('persentase')
                    ->label('Persentase')
                    ->getStateUsing(function ($record) {
                        $target = (float) ($record->target ?? 0);
                        $realisasi = (float) ($record->realisasi ?? 0);
                        if ($target > 0) {
                            $percent = ($realisasi / $target) * 100;

                            return number_format($percent, 2).'%';
                        }

                        return '0%';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Gender')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ])
                    ->query(function ($query, $filter) {
                        $data = $filter->getState();

                        return $query->when($data['value'], fn ($q) => $q->whereHas('murobbi', function ($q) use ($data) {
                            $q->where('gender', $data['value']);
                        }));
                    }),
                Tables\Filters\SelectFilter::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->default($defaultTahun)
                    ->options(fn () => array_combine($general->years, $general->years))
                    ->query(fn ($query, $filter) => $query->when($filter->getState()['value'], fn ($q, $value) => $q->where('tahun_ajaran', $value))),
                Tables\Filters\SelectFilter::make('semester')
                    ->label('Semester')
                    ->default($defaultSemester)
                    ->options([
                        '1' => 'Semester 1',
                        '2' => 'Semester 2',
                    ])
                    ->query(fn ($query, $filter) => $query->when($filter->getState()['value'], fn ($q, $value) => $q->where('semester', $value))),
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options(fn () => \App\Models\Tahfidz\JournalPerformance::query()
                        ->where('tahun_ajaran', $defaultTahun)
                        ->where('semester', $defaultSemester)
                        ->where('jenis_periode', request()->query('Tab', 'weekly'))
                        ->select(['id', 'awal', 'akhir'])
                        ->orderBy('awal', 'desc')
                        ->get()
                        ->mapWithKeys(function ($record) {
                            $awal = $record->awal?->format('d-m-Y') ?? '-';
                            $akhir = $record->akhir?->format('d-m-Y') ?? '-';
                            $start = $record->awal?->format('Y-m-d') ?? '-';
                            $end = $record->akhir?->format('Y-m-d') ?? '-';

                            // return [$record->id => "$awal - $akhir"];
                            return ["$start--$end" => "$awal - $akhir"];
                        })
                        ->unique()
                        ->toArray())
                    ->query(function ($query, $filter) {
                        $query->when($filter->getState()['value'], function ($q, $value) {
                            [$from, $until] = explode('--', $value);
                            $q->where('awal', '>=', $from)
                                ->where('akhir', '<=', $until);
                        });
                    }),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('view_murobbi')
                    ->label('Lihat Murobbi')
                    ->url(fn ($record) => ViewMurobbi::getUrl(['record' => $record->murobbi_id]).'?relation=1')
                    ->icon('heroicon-m-eye'),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
