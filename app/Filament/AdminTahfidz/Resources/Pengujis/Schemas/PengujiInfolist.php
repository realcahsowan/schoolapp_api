<?php

namespace App\Filament\AdminTahfidz\Resources\Pengujis\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PengujiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // // TextEntry::make('school_id')
                // //     ->numeric(),
                // // TextEntry::make('employee_id')
                // //     ->numeric()
                //     ->placeholder('-'),
                TextEntry::make('nama'),
                // TextEntry::make('tahun_ajaran'),
                // TextEntry::make('semester')
                //     ->numeric(),
                TextEntry::make('gender')
                    ->badge(),
                TextEntry::make('total_students')
                    ->numeric(),
                TextEntry::make('percentage')
                    ->numeric(),
                // TextEntry::make('created_at')
                //     ->dateTime()
                //     ->placeholder('-'),
                // TextEntry::make('updated_at')
                //     ->dateTime()
                //     ->placeholder('-'),
            ]);
    }
}
