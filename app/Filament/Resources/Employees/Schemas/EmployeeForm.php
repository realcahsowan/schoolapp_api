<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Institution;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pribadi')
                    ->columns(3)
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Pegawai')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('nik')
                            ->label('NIK')
                            ->required(),
                        TextInput::make('nip')
                            ->label('NIP'),
                        Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                            ])
                            ->required(),
                        TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir'),
                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir'),
                        TextInput::make('telepon')
                            ->label('Telepon')
                            ->tel(),
                        Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
                Section::make('Data Kepegawaian')
                    ->columns(3)
                    ->schema([
                        Select::make('institution_id')
                            ->label('Institusi')
                            ->options(Institution::pluck('nama', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('pendidikan_terakhir')
                            ->label('Pendidikan Terakhir'),
                        TextInput::make('angkatan_stipi')
                            ->label('Angkatan STIPI'),
                        TextInput::make('nuptk')
                            ->label('NUPTK'),
                        TextInput::make('peg_id')
                            ->label('ID Pegawai'),
                        Select::make('golongan_darah')
                            ->label('Golongan Darah')
                            ->options([
                                'A' => 'A',
                                'B' => 'B',
                                'AB' => 'AB',
                                'O' => 'O',
                            ]),
                        DatePicker::make('tanggal_mulai_bekerja')
                            ->label('Tanggal Mulai Bekerja'),
                        Select::make('status_perkawinan')
                            ->label('Status Perkawinan')
                            ->options([
                                'Belum Kawin' => 'Belum Kawin',
                                'Kawin' => 'Kawin',
                                'Cerai Hidup' => 'Cerai Hidup',
                                'Cerai Mati' => 'Cerai Mati',
                            ]),
                    ])->columnSpanFull(),
                Section::make('Data Keluarga')
                    ->columns(3)
                    ->schema([
                        TextInput::make('nama_ayah')
                            ->label('Nama Ayah'),
                        TextInput::make('nama_ibu')
                            ->label('Nama Ibu'),
                        TextInput::make('nama_pasangan')
                            ->label('Nama Pasangan'),
                        TextInput::make('jumlah_anak')
                            ->label('Jumlah Anak')
                            ->numeric(),
                    ])->columnSpanFull(),
            ]);
    }
}
