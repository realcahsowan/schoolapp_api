<?php

namespace App\Filament\AdminTahfidz\Resources\Students\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use App\Settings\GeneralSettings;
use App\Models\Tahfidz\MemorizationSummary;
use Illuminate\Support\Arr;

class MemorizationSummariesRelationManager extends RelationManager
{
    protected static string $relationship = 'memorizationSummaries';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('periode')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('periode')
            ->columns([
                TextColumn::make('tahun_ajaran'),
                TextColumn::make('semester'),
                TextColumn::make('periode'),
                TextColumn::make('awal_periode'),
                TextColumn::make('akhir_periode'),
                TextColumn::make('total_halaman'),
                TextColumn::make('total_ayat'),
            ])
            ->filters([
                SelectFilter::make('tahun_ajaran')
                    ->label('Tahun Ajaran')
                    ->options(fn() => MemorizationSummary::query()->distinct('tahun_ajaran')->pluck('tahun_ajaran', 'tahun_ajaran')->toArray())
                    ->default(fn() => app(GeneralSettings::class)->tahun_ajaran),
                SelectFilter::make('semester')
                    ->label('Semester')
                    ->options(fn() => MemorizationSummary::query()->distinct('semester')->pluck('semester', 'semester')->toArray())
                        ->default(fn() => app(GeneralSettings::class)->semester),
                SelectFilter::make('periode')
                    ->label('Periode')
                    ->options(fn() => MemorizationSummary::query()->distinct('periode')->pluck('periode', 'periode')->toArray()),
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('viewDetail')
                    ->label('Lihat Detail')
                    ->modalHeading('Detail Memorization Summary')
                    ->modalCancelAction(false)
                    ->modalSubmitAction(false)
                    ->schema(fn(MemorizationSummary $record) => [
                        Section::make()
                            ->columns(3)
                            ->schema([
                                TextEntry::make('periode')->label('Periode'),
                                TextEntry::make('awal_periode')->label('Awal Periode'),
                                TextEntry::make('akhir_periode')->label('Akhir Periode'),
                                TextEntry::make('total_halaman')->label('Total Halaman'),
                                TextEntry::make('total_ayat')->label('Total Ayat'),
                                TextEntry::make('total_surat')->label('Total Surat'),
                                TextEntry::make('total_juz')->label('Total Juz'),
                                TextEntry::make('detail_halaman')->label('Detail Halaman'),
                                KeyValueEntry::make('ringkasan')
                                    ->keyLabel('Jenis Hafalan')
                                    ->valueLabel('Halaman')
                                    ->columnSpanFull()
                                    ->label('Ringkasan')
                                    ->getStateUsing(function ($record) {
                                        $result = [];
                                        foreach (['tahsin', 'hafalan_baru', 'hafalan_murojaah'] as $key) {
                                            $pages = Arr::get($record->ringkasan, $key . '.halaman', []);
                                            $result[$key] = implode(', ', $pages);
                                        }
                                        return $result;
                                    }),
                                KeyValueEntry::make('presensi')
                                    ->keyLabel('Jenis')
                                    ->valueLabel('Jumlah')
                                    ->columnSpanFull()
                                    ->label('Presensi')
                                    ->getStateUsing(function ($record) {
                                        return [
                                            'hadir' => Arr::get($record->ringkasan, 'kehadiran_detail.hadir', 0),
                                            'izin' => Arr::get($record->ringkasan, 'kehadiran_detail.izin', 0),
                                            'sakit' => Arr::get($record->ringkasan, 'kehadiran_detail.sakit', 0),
                                        ];
                                    }),
                                KeyValueEntry::make('kurikulum_hb')
                                    ->keyLabel('Surat')
                                    ->valueLabel('Detail')
                                    ->columnSpanFull()
                                    ->label('Kurikulum Hafalan Baru')
                                    ->getStateUsing(function ($record) {
                                        $items = Arr::get($record->kurikulum, 'hb', []);
                                        $result = [];

                                        foreach ($items as $surat => $ayats) {
                                            $result[$surat] = implode(', ', $ayats);
                                        }
                                        return $result;
                                    }),
                                KeyValueEntry::make('kurikulum_hm')
                                    ->keyLabel('Surat')
                                    ->valueLabel('Detail')
                                    ->columnSpanFull()
                                    ->label('Kurikulum Hafalan Murojaah')
                                    ->getStateUsing(function ($record) {
                                        $items = Arr::get($record->kurikulum, 'hm', []);
                                        $result = [];

                                        foreach ($items as $surat => $ayats) {
                                            $result[$surat] = implode(', ', $ayats);
                                        }
                                        return $result;
                                    }),
                            ]),
                    ]),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
