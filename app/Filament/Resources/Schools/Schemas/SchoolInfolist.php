<?php

namespace App\Filament\Resources\Schools\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Sekolah')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('nama')
                            ->label('Nama Sekolah')
                            ->columnSpanFull(),
                        TextEntry::make('nsm')
                            ->label('NSM')
                            ->placeholder('-'),
                        TextEntry::make('npsn')
                            ->label('NPSN')
                            ->placeholder('-'),
                        TextEntry::make('jenjang')
                            ->label('Jenjang')
                            ->placeholder('-'),
                        TextEntry::make('telepon')
                            ->label('Telepon')
                            ->placeholder('-'),
                        TextEntry::make('alamat')
                            ->label('Alamat Lengkap')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }
}
