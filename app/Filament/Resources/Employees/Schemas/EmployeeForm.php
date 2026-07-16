<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Pegawai')
                    ->required(),
                TextInput::make('nik')
                    ->label('NIK')
                    ->required(),
                TextInput::make('nip')
                    ->label('NIP'),
                TextInput::make('tempat_lahir')
                    ->label('Tempat Lahir'),
                DatePicker::make('tanggal_lahir')
                    ->label('Tanggal Lahir'),
                TextInput::make('gender')
                    ->label('Jenis Kelamin'),
                Textarea::make('alamat')
                    ->label('Alamat Lengkap')
                    ->columnSpanFull(),
                TextInput::make('telepon')
                    ->label('Telepon')
                    ->tel(),
                TextInput::make('file_foto')
                    ->label('Foto'),
                TextInput::make('file_signature')
                    ->label('Tanda Tangan'),
                TextInput::make('institution_id')
                    ->label('Institusi')
                    ->numeric(),
                TextInput::make('pendidikan_terakhir')
                    ->label('Pendidikan Terakhir'),
                TextInput::make('angkatan_stipi')
                    ->label('Angkatan STIPI'),
                TextInput::make('nuptk')
                    ->label('NUPTK'),
                TextInput::make('peg_id')
                    ->label('ID Pegawai'),
                TextInput::make('golongan_darah')
                    ->label('Golongan Darah'),
                DatePicker::make('tanggal_mulai_bekerja')
                    ->label('Tanggal Mulai Bekerja'),
                TextInput::make('status_perkawinan')
                    ->label('Status Perkawinan'),
                TextInput::make('nama_ayah')
                    ->label('Nama Ayah'),
                TextInput::make('nama_ibu')
                    ->label('Nama Ibu'),
                TextInput::make('nama_pasangan'),
                TextInput::make('jumlah_anak')
                    ->numeric(),
                TextInput::make('riwayat_mengajar'),
            ]);
    }
}
