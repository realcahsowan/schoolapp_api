<?php

namespace App\Filament\Resources\Schools\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SchoolInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama'),
                TextEntry::make('nsm')
                    ->placeholder('-')
                    ->label('NSM'),
                TextEntry::make('npsn')
                    ->placeholder('-')
                    ->label('NPSN'),
                TextEntry::make('alamat')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('telepon')
                    ->placeholder('-'),
                TextEntry::make('jenjang')
                    ->placeholder('-'),
                // TextEntry::make('rdm_id')
                //     ->placeholder('-'),
                // TextEntry::make('rdm_db')
                //     ->placeholder('-'),
                // TextEntry::make('alias')
                //     ->placeholder('-'),
                // TextEntry::make('fullname')
                //     ->placeholder('-'),
                // TextEntry::make('institution_id')
                //     ->numeric()
                //     ->placeholder('-'),
                // TextEntry::make('created_at')
                //     ->dateTime()
                //     ->placeholder('-'),
                // TextEntry::make('updated_at')
                //     ->dateTime()
                //     ->placeholder('-'),
            ])
            ->columns(3);
    }
}
