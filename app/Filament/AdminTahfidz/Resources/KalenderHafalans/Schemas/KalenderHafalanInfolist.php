<?php

namespace App\Filament\AdminTahfidz\Resources\KalenderHafalans\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class KalenderHafalanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // TextEntry::make('school_id')
                //     ->numeric(),
                TextEntry::make('tahun_ajaran')
                    ->placeholder('-'),
                TextEntry::make('semester')
                    ->numeric()
                    ->placeholder('-'),
                // TextEntry::make('year')
                //     ->numeric()
                //     ->placeholder('-'),
                // TextEntry::make('month')
                //     ->numeric()
                //     ->placeholder('-'),
                TextEntry::make('week')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('day')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('tanggal')
                    ->date()
                    ->placeholder('-'),
                IconEntry::make('is_hp_only')
                    ->boolean()
                    ->placeholder('-'),
                IconEntry::make('is_weekly_examination')
                    ->boolean()
                    ->placeholder('-'),
                IconEntry::make('is_hp_only')
                    ->label('Hafalan Pagi Saja')
                    ->boolean()
                    ->placeholder('-'),

                // IconEntry::make('is_disabled')
                //     ->boolean()
                //     ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                \Filament\Schemas\Components\Section::make()
                    ->label('Kurikulum Hafalan Pagi')
                    ->columnSpanFull()
                    ->components([
                        \Filament\Infolists\Components\RepeatableEntry::make('hp_summary_for_infolist')
                            ->label('Setoran per Kelas')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('kelas')
                                    ->label('Kelas')
                                    ->placeholder('-'),
                                \Filament\Infolists\Components\RepeatableEntry::make('details')
                                    ->label('Setoran')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('jenis')->label('Jenis')
                                            ->formatStateUsing(fn($state) => match ($state) {
                                                'hb' => 'Hafalan Baru',
                                                'hm' => 'Hafalan Mengulang',
                                                default => '-',
                                            }),
                                        \Filament\Infolists\Components\TextEntry::make('surat')->label('Surat')->placeholder('-'),
                                        \Filament\Infolists\Components\TextEntry::make('awal')->label('Awal')->placeholder('-'),
                                        \Filament\Infolists\Components\TextEntry::make('akhir')->label('Akhir')->placeholder('-'),
                                    ])
                                    ->placeholder('-')
                                    ->columns(4),
                            ])
                            ->placeholder('-')
                        ->grid(3),
                    ]),
                \Filament\Schemas\Components\Section::make()
                    ->label('Kurikulum Hafalan Sore')
                    ->columnSpanFull()
                    ->components([
                        \Filament\Infolists\Components\RepeatableEntry::make('hs_summary_for_infolist')
                            ->label('Setoran per Kelas')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('kelas')
                                    ->label('Kelas')
                                    ->placeholder('-'),
                                \Filament\Infolists\Components\RepeatableEntry::make('details')
                                    ->label('Setoran')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('jenis')->label('Jenis')
                                            ->formatStateUsing(fn($state) => match ($state) {
                                                'hb' => 'Hafalan Baru',
                                                'hm' => 'Hafalan Mengulang',
                                                default => '-',
                                            }),
                                        \Filament\Infolists\Components\TextEntry::make('surat')->label('Surat')->placeholder('-'),
                                        \Filament\Infolists\Components\TextEntry::make('awal')->label('Awal')->placeholder('-'),
                                        \Filament\Infolists\Components\TextEntry::make('akhir')->label('Akhir')->placeholder('-'),
                                    ])
                                    ->placeholder('-')
                                    ->columns(4),
                            ])
                            ->placeholder('-')
                        ->grid(3),
                    ]),


            ])
            // ->sections([
            // ])
            ->columns(3);
    }
}
