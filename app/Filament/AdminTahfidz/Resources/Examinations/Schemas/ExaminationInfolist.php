<?php

namespace App\Filament\AdminTahfidz\Resources\Examinations\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ExaminationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('student.nama')
                    ->label('Student Name'),

                TextEntry::make('juz')
                    ->label('Juz'),

                TextEntry::make('score')
                    ->label('Score'),

                IconEntry::make('is_nulled')
                    ->label('Tidak Disetor?'),

                IconEntry::make('is_locked')
                    ->label('Dikunci?'),
            ])->columns(3);
    }
}
