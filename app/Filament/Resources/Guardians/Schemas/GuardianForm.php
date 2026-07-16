<?php

namespace App\Filament\Resources\Guardians\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GuardianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Wali')
                    ->required(),
                // TextInput::make('nik')
                //     ->label('NIK'),
                TextInput::make('tempat_lahir')
                    ->label('Tempat Lahir'),
                DatePicker::make('tanggal_lahir')
                    ->label('Tanggal Lahir'),
                \Filament\Forms\Components\Select::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ]),
                \Filament\Forms\Components\Select::make('relation_type')
                    ->label('Jenis Hubungan')
                    ->options([
                        'ayah' => 'Ayah',
                        'ibu' => 'Ibu',
                    ]),
                \Filament\Forms\Components\Select::make('relation_status')
                    ->label('Status Hubungan')
                    ->options([
                        'kandung' => 'Kandung',
                        'tiri' => 'Tiri',
                        'angkat' => 'Angkat',
                    ]),

                Textarea::make('alamat')
                    ->label('Alamat Lengkap')
                    ->columnSpanFull(),
                TextInput::make('telepon')
                    ->label('Telepon')
                    ->tel(),
                // TextInput::make('file_foto')
                //     ->label('Foto'),
                // TextInput::make('agama')
                //     ->label('Agama'),
                TextInput::make('pendidikan')
                    ->label('Pendidikan'),
                TextInput::make('pekerjaan')
                    ->label('Pekerjaan'),
                Toggle::make('is_alive')
                    ->label('Masih Hidup')
                    ->required(),
                // Toggle::make('modifed_by_owner')
                //     ->label('Dimodifikasi oleh Wali')
                //     ->required(),
                // DateTimePicker::make('telepon_verified_at')
                //     ->label('Verifikasi Telepon'),
                // TextInput::make('telepon_verification_code')
                //     ->label('Kode Verifikasi')
                //     ->tel(),
                // DateTimePicker::make('telepon_verification_code_expired_at'),
            ])
            ->columns(3);
    }
}
