<?php

namespace App\Filament\Resources\Dormitories\Schemas;

use App\Models\Institution;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DormitoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Asrama')
                    ->columns(3)
                    ->schema([
                        Select::make('institution_id')
                            ->label('Institusi')
                            ->options(Institution::pluck('nama', 'id'))
                            ->searchable()
                            ->required(),
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
                            ->label('Penuh'),
                    ])->columnSpanFull(),
            ]);
    }
}
