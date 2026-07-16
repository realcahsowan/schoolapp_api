<?php

namespace App\Filament\AdminTahfidz\Resources\Students\Schemas;

use App\Settings\GeneralSettings;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $tahunAjaran = app(GeneralSettings::class)->tahun_ajaran;
        $semester = app(GeneralSettings::class)->semester;

        return $schema
            ->components([
                TextEntry::make('nama'),
                // TextEntry::make('nis')
                //     ->label('NIS')
                //     ->placeholder('-'),
                TextEntry::make('nisn')
                    ->label('NISN')
                    ->placeholder('-'),
                // TextEntry::make('nik')
                //     ->placeholder('-'),
                // TextEntry::make('tempat_lahir')
                //     ->placeholder('-'),
                // TextEntry::make('tanggal_lahir')
                //     ->date()
                //     ->placeholder('-'),
                TextEntry::make('gender')
                    ->formatStateUsing(fn ($state) => $state === 'male' ? 'Laki-laki' : ($state === 'female' ? 'Perempuan' : '-'))
                    ->placeholder('-'),
                TextEntry::make('classroom.nama')
                    ->label('Kelas'),
                TextEntry::make('active_dormitory.name')
                    ->label('Asrama'),
                TextEntry::make('active_dormitory.pivot.room')
                    ->label('Kamar'),
                TextEntry::make('murobbis.nama')
                    ->label('Murobbi'),
                TextEntry::make('rapor.periodic_score')
                    ->label('Nilai Periodik')
                    ->getStateUsing(function ($record) {
                        $periodicScore = $record->rapor?->periodic_score;
                        $periodicCount = $record->penilaianPeriodik->where(function ($p) {
                            return $p->tahun_ajaran == app(GeneralSettings::class)->tahun_ajaran && $p->semester == app(GeneralSettings::class)->semester;
                        })->count();

                        return $periodicScore.' ('.$periodicCount.')';
                    }),
                TextEntry::make('rapor.pas_score')
                    ->label('Nilai PAS'),
                TextEntry::make('rapor.sa_score')
                    ->label('Nilai Muwashalat Ayat'),
                // TextEntry::make('alamat')
                //     ->placeholder('-')
                //     ->columnSpanFull(),
                // TextEntry::make('telepon')
                //     ->placeholder('-'),
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
                // IconEntry::make('is_graduated')
                //     ->boolean(),
                // IconEntry::make('is_beasiswa')
                //     ->boolean(),
                // IconEntry::make('is_active')
                //     ->boolean(),
                // IconEntry::make('has_siblings')
                //     ->boolean(),
                // TextEntry::make('virtual_account')
                //     ->placeholder('-'),
                // TextEntry::make('agama')
                //     ->placeholder('-'),
                // TextEntry::make('file_foto')
                //     ->placeholder('-'),
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
            ->columns(3);
    }
}
