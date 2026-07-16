<?php

namespace App\Filament\Resources\Schools\Schemas;

use App\Traits\SekolahTrait;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SchoolForm
{
    use SekolahTrait;

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Sekolah')
                    ->required(),
                TextInput::make('nsm')
                    ->label('NSM'),
                TextInput::make('npsn')
                    ->label('NPSN'),
                Select::make('jenjang')
                    ->options(static::getJenjangOptions())
                    ->required(),
                Textarea::make('alamat')
                    ->label('Alamat Lengkap')
                    ->columnSpanFull(),
                TextInput::make('telepon')
                    ->label('Telepon')
                    ->tel(),
                FileUpload::make('logo')
                    ->label('Logo'),
                // TextInput::make('rdm_id')
                //     ->label('ID RDM'),
                // TextInput::make('rdm_db')
                //     ->label('Database RDM'),
                // TextInput::make('alias')
                //     ->label('Alias'),
                // TextInput::make('fullname')
                //     ->label('Nama Lengkap'),
                // TextInput::make('institution_id')
                //     ->label('Institusi')
                //     ->numeric(),
            ]);
    }
}
