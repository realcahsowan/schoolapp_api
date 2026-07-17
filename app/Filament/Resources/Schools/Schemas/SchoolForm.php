<?php

namespace App\Filament\Resources\Schools\Schemas;

use App\Traits\SekolahTrait;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolForm
{
    use SekolahTrait;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Sekolah')
                    ->columns(3)
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Sekolah')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('nsm')
                            ->label('NSM'),
                        TextInput::make('npsn')
                            ->label('NPSN'),
                        Select::make('jenjang')
                            ->label('Jenjang')
                            ->options(static::getJenjangOptions())
                            ->required(),
                        TextInput::make('telepon')
                            ->label('Telepon')
                            ->tel(),
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->columnSpanFull(),
                        Textarea::make('alamat')
                            ->label('Alamat Lengkap')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }
}
