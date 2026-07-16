<?php

namespace App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;

class KurikulumHafalanInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('school.nama')
                    ->label('Sekolah')
                    ->placeholder('-'),
                // TextEntry::make('tahun_ajaran')
                //     ->label('Tahun Ajaran')
                //     ->placeholder('-'),
                // TextEntry::make('semester')
                //     ->label('Semester')
                //     ->placeholder('-'),
                TextEntry::make('grade')
                    ->label('Grade')
                    ->placeholder('-'),
                TextEntry::make('program')
                    ->label('Program')
                    ->placeholder('-'),
                TextEntry::make('total_ayat_hafalan_baru')
                    ->label('Total Ayat Hafalan Baru')
                    ->placeholder('-'),
                TextEntry::make('total_surat_hafalan_baru')
                    ->label('Total Surat Hafalan Baru')
                    ->placeholder('-'),
                TextEntry::make('total_juz_hafalan_baru')
                    ->label('Total Juz Hafalan Baru')
                    ->placeholder('-'),
                RepeatableEntry::make('detail_hafalan_baru')
                    ->getStateUsing(function ($record) {
                        $result = [];
                        foreach ($record->detail_hafalan_baru as $surat => $ranges) {
                            $result[] = [
                                'surat' => $surat,
                                'ayat' =>  collect($ranges)->flatten(),
                                // 'juz' => '-',
                            ];
                        }
                        return $result;
                    })
                    ->columnSpanFull()
                    ->label('Detail Hafalan Baru')
                    ->table([
                        TableColumn::make('Surat'),
                        TableColumn::make('Ayat'),
                        // TableColumn::make('Juz'),
                    ])
                    ->schema([
                        TextEntry::make('surat')->label('Surat')->placeholder('-'),
                        TextEntry::make('ayat')->label('Ayat')->placeholder('-'),
                        // TextEntry::make('juz')->label('Juz')->placeholder('-'),
                    ])
                   ->placeholder('-'),
                TextEntry::make('total_ayat_hafalan_murojaah')
                     ->label('Total Ayat Hafalan Murojaah')
                     ->placeholder('-'),
                TextEntry::make('total_surat_hafalan_murojaah')
                    ->label('Total Surat Hafalan Murojaah')
                    ->placeholder('-'),
                TextEntry::make('total_juz_hafalan_murojaah')
                    ->label('Total Juz Hafalan Murojaah')
                    ->placeholder('-'),
                RepeatableEntry::make('detail_hafalan_murojaah')
                    ->getStateUsing(function ($record) {
                        $result = [];
                        foreach ($record->detail_hafalan_murojaah as $surat => $ranges) {
                            $result[] = [
                                'surat' => $surat,
                                'ayat' =>  collect($ranges)->flatten(),
                                // 'juz' => '-',
                            ];
                        }
                        return $result;
                    })
                    ->columnSpanFull()
                    ->label('Detail Hafalan Murojaah')
                    ->table([
                        TableColumn::make('Surat'),
                        TableColumn::make('Ayat'),
                        // TableColumn::make('Juz'),
                    ])
                    ->schema([
                        TextEntry::make('surat')->label('Surat')->placeholder('-'),
                        TextEntry::make('ayat')->label('Ayat')->placeholder('-'),
                        // TextEntry::make('juz')->label('Juz')->placeholder('-'),
                    ])
                   ->placeholder('-'),

            ]);
    }
}
