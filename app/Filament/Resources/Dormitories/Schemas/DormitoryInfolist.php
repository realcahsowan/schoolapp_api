<?php

namespace App\Filament\Resources\Dormitories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DormitoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Asrama')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('institution.nama')
                            ->label('Institusi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('name')
                            ->label('Nama Asrama')
                            ->columnSpanFull(),
                        TextEntry::make('capacity')
                            ->label('Kapasitas')
                            ->numeric(),
                        TextEntry::make('rooms')
                            ->label('Jumlah Kamar')
                            ->numeric(),
                        IconEntry::make('is_full')
                            ->label('Penuh')
                            ->boolean(),
                    ]),
                Section::make('Informasi Lainnya')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
