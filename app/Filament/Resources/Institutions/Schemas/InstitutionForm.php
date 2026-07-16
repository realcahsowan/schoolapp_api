<?php

namespace App\Filament\Resources\Institutions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InstitutionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Institusi')
                    ->required(),
                TextInput::make('akta')
                    ->label('Nomor Akta')
                    ->required(),
                TextInput::make('telepon')
                    ->label('Telepon')
                    ->tel()
                    ->required(),
                Textarea::make('alamat')
                    ->label('Alamat Lengkap')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
