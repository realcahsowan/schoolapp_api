<?php

namespace App\Filament\AdminTahfidz\Resources\Students\RelationManagers;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Actions\ViewAction;
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
use Illuminate\Database\Eloquent\Builder;

class JournalsRelationManager extends RelationManager
{
    protected static string $relationship = 'journals';
    protected static ?string $title = 'Mutabaah Harian';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tanggal')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        $tahunAjaran = app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
        $semester = app(\App\Settings\GeneralSettings::class)->semester;

        return $table
            ->modifyQueryUsing(
                fn(Builder $query) => $query->where('tahun_ajaran', $tahunAjaran)
                    ->where('semester', $semester)
            )
            ->recordTitleAttribute('tanggal')
            ->columns([
                TextColumn::make('tanggal'),
                TextColumn::make('waktu'),
                TextColumn::make('kehadiran'),
                TextColumn::make('status'),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('journals_filter')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal'),
                        \Filament\Forms\Components\Select::make('waktu')
                            ->label('Waktu')
                            ->options([
                                'pagi' => 'Pagi',
                                'sore' => 'Sore',
                            ])
                            ->searchable(),
                        \Filament\Forms\Components\Select::make('kehadiran')
                            ->options(fn() => \App\Models\Tahfidz\Journal::query()->distinct('kehadiran')->pluck('kehadiran', 'kehadiran')->toArray())
                            ->label('Kehadiran')
                            ->searchable(),
                        \Filament\Forms\Components\Select::make('status')
                            ->options(fn() => \App\Models\Tahfidz\Journal::query()->distinct('status')->pluck('status', 'status')->toArray())
                            ->label('Status')
                            ->searchable(),
                    ])
                    ->query(function ($query, $data) {
                        if (!empty($data['tanggal'])) {
                            $query->whereDate('tanggal', $data['tanggal']);
                        }
                        if (!empty($data['waktu'])) {
                            $query->where('waktu', $data['waktu']);
                        }
                        if (!empty($data['kehadiran'])) {
                            $query->where('kehadiran', $data['kehadiran']);
                        }
                        if (!empty($data['status'])) {
                            $query->where('status', $data['status']);
                        }
                    }),
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Detail Mutabaah')
                    ->infolist([
                        Section::make('Informasi Mutabaah')
                            ->schema([
                                TextEntry::make('tanggal'),
                                TextEntry::make('waktu'),
                                TextEntry::make('kehadiran'),
                                TextEntry::make('status'),
                                TextEntry::make('catatan')->columnSpanFull(),
                            ])->columns(2),
                        Section::make('Detail Capaian')
                            ->schema([
                                RepeatableEntry::make('detail_capaian')
                                    ->label('')
                                    ->schema([
                                        TextEntry::make('jenis')
                                            ->formatStateUsing(fn($state) => match ($state) {
                                                'hb' => 'Hafalan Baru',
                                                'hm' => 'Hafalan Murojaah',
                                                default => $state,
                                            }),
                                        TextEntry::make('surat'),
                                        TextEntry::make('awal'),
                                        TextEntry::make('akhir'),
                                    ])->columns(4),
                            ]),
                        Section::make('Detail Extra')
                            ->schema([
                                RepeatableEntry::make('detail_extra')
                                    ->label('')
                                    ->schema([
                                        TextEntry::make('jenis')
                                            ->formatStateUsing(fn($state) => match ($state) {
                                                'hb' => 'Hafalan Baru',
                                                'hm' => 'Hafalan Murojaah',
                                                default => $state,
                                            }),
                                        TextEntry::make('surat'),
                                        TextEntry::make('awal'),
                                        TextEntry::make('akhir'),
                                    ])->columns(4),
                            ]),
                        Section::make('Detail Khusus')
                            ->schema([
                                TextEntry::make('detail_khusus.jenis'),
                                TextEntry::make('detail_khusus.halaman_awal'),
                                TextEntry::make('detail_khusus.halaman_akhir'),
                            ])->columns(3),
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
