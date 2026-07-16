<?php

namespace App\Filament\AdminTahfidz\Resources\Murobbis\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MurobbiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_id')
                    ->required()
                    ->numeric(),
                TextInput::make('school_id')
                    ->required()
                    ->numeric(),
                TextInput::make('nama')
                    ->required(),
                TextInput::make('nama_pendek'),
                TextInput::make('gender'),
                TextInput::make('tahun_ajaran'),
                TextInput::make('semester')
                    ->numeric(),
            ]);
    }
}
