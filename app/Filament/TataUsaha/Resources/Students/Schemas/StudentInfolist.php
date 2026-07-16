<?php

namespace App\Filament\TataUsaha\Resources\Students\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama'),
                TextEntry::make('nis')
                    ->placeholder('-'),
                TextEntry::make('nisn')
                    ->placeholder('-'),
                // TextEntry::make('nik')
                //     ->placeholder('-'),
                TextEntry::make('classroom.nama')
                    ->label('Kelas')
                    ->placeholder('-'),
                TextEntry::make('tempat_lahir')
                    ->placeholder('-'),
                TextEntry::make('tanggal_lahir')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('gender')
                    ->formatStateUsing(fn($state) => $state === 'male' ? 'Laki-laki' : ($state === 'female' ? 'Perempuan' : '-'))
                    ->placeholder('-'),
                TextEntry::make('alamat')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('telepon')
                    ->placeholder('-'),
                // TextEntry::make('anak_ke')
                //     ->numeric()
                //     ->placeholder('-'),
                // TextEntry::make('jumlah_saudara')
                //     ->numeric()
                //     ->placeholder('-'),
                // TextEntry::make('sekolah_asal')
                //     ->placeholder('-'),
                // TextEntry::make('nomor_ijazah')
                //     ->placeholder('-'),
                IconEntry::make('is_graduated')
                    ->boolean(),
                // IconEntry::make('is_beasiswa')
                //     ->boolean(),
                IconEntry::make('is_active')
                    ->boolean(),
                IconEntry::make('has_siblings')
                    ->boolean(),
                // TextEntry::make('virtual_account')
                //     ->placeholder('-'),
                // TextEntry::make('agama')
                //     ->placeholder('-'),
                TextEntry::make('file_foto')
                    ->placeholder('-'),
                // TextEntry::make('pendidikan')
                //     ->placeholder('-'),
                // TextEntry::make('kode_emis')
                //     ->placeholder('-'),
                // TextEntry::make('propinsi')
                //     ->placeholder('-'),
                // TextEntry::make('kabupaten_kota')
                //     ->placeholder('-'),
                // TextEntry::make('kecamatan')
                //     ->placeholder('-'),
                // TextEntry::make('kelurahan')
                //     ->placeholder('-'),
                // TextEntry::make('kodepos')
                //     ->placeholder('-'),
                // TextEntry::make('tingkat_id')
                //     ->numeric()
                //     ->placeholder('-'),
                // TextEntry::make('classroom_id')
                //     ->numeric()
                //     ->placeholder('-'),
                // TextEntry::make('created_at')
                //     ->dateTime()
                //     ->placeholder('-'),
                // TextEntry::make('updated_at')
                //     ->dateTime()
                //     ->placeholder('-'),
            ])
            ->columns(4);
    }
}
