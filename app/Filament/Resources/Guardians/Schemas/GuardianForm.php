<?php

namespace App\Filament\Resources\Guardians\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GuardianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pribadi')
                    ->columns(3)
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Wali')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir'),
                        DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir'),
                        Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                            ]),
                        TextInput::make('telepon')
                            ->label('Telepon')
                            ->tel(),
                        Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
                Section::make('Data Lainnya')
                    ->columns(3)
                    ->schema([
                        Select::make('relation_type')
                            ->label('Jenis Hubungan')
                            ->options([
                                'ayah' => 'Ayah',
                                'ibu' => 'Ibu',
                            ]),
                        Select::make('relation_status')
                            ->label('Status Hubungan')
                            ->options([
                                'kandung' => 'Kandung',
                                'tiri' => 'Tiri',
                                'angkat' => 'Angkat',
                            ]),
                        TextInput::make('pendidikan')
                            ->label('Pendidikan'),
                        TextInput::make('pekerjaan')
                            ->label('Pekerjaan'),
                        Toggle::make('is_alive')
                            ->label('Masih Hidup'),
                    ])->columnSpanFull(),
            ]);
    }
}
