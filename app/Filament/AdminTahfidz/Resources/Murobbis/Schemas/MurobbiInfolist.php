<?php

namespace App\Filament\AdminTahfidz\Resources\Murobbis\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MurobbiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components([
                // TextEntry::make('employee_id')
                //     ->numeric(),
                // TextEntry::make('school_id')
                //     ->numeric(),
                TextEntry::make('nama'),
                // TextEntry::make('nama_pendek')
                //     ->placeholder('-'),
                TextEntry::make('gender')
                    ->formatStateUsing(fn($state) => $state === 'male' ? 'Laki-laki' : ($state === 'female' ? 'Perempuan' : '-'))
                    ->placeholder('-'),
                TextEntry::make('tahun_ajaran')
                    ->placeholder('-'),
                TextEntry::make('semester')
                    ->numeric()
                    ->placeholder('-'),
                // TextEntry::make('created_at')
                //     ->dateTime()
                //     ->placeholder('-'),
                // TextEntry::make('updated_at')
                //     ->dateTime()
                //     ->placeholder('-'),
            ]);
    }
}
