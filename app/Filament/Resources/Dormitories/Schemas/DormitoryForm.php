<?php

namespace App\Filament\Resources\Dormitories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DormitoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('institution_id')
                    ->label('Institusi')
                    ->numeric(),
                TextInput::make('name')
                    ->label('Nama Asrama')
                    ->required(),
                TextInput::make('capacity')
                    ->label('Kapasitas')
                    ->required()
                    ->numeric(),
                TextInput::make('rooms')
                    ->label('Jumlah Kamar')
                    ->required()
                    ->numeric(),
                Toggle::make('is_full')
                    ->label('Penuh')
                    ->required(),
            ]);
    }
}
