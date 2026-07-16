<?php

namespace App\Filament\AdminTahfidz\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required(),
                TextInput::make('nis'),
                TextInput::make('nisn'),
                TextInput::make('nik'),
                TextInput::make('tempat_lahir'),
                DatePicker::make('tanggal_lahir'),
                TextInput::make('gender'),
                Textarea::make('alamat')
                    ->columnSpanFull(),
                TextInput::make('telepon')
                    ->tel(),
                TextInput::make('anak_ke')
                    ->numeric(),
                TextInput::make('jumlah_saudara')
                    ->numeric(),
                TextInput::make('sekolah_asal'),
                TextInput::make('nomor_ijazah'),
                TextInput::make('riwayat_kelas'),
                Toggle::make('is_graduated')
                    ->required(),
                Toggle::make('is_beasiswa')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('has_siblings')
                    ->required(),
                TextInput::make('virtual_account'),
                TextInput::make('agama'),
                TextInput::make('file_foto'),
                TextInput::make('pendidikan'),
                TextInput::make('kode_emis'),
                TextInput::make('propinsi'),
                TextInput::make('kabupaten_kota'),
                TextInput::make('kecamatan'),
                TextInput::make('kelurahan'),
                TextInput::make('kodepos'),
                TextInput::make('tingkat_id')
                    ->numeric(),
                TextInput::make('classroom_id')
                    ->numeric(),
            ]);
    }
}
