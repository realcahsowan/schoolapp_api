<?php

namespace App\Filament\TataUsaha\Resources\Guardians\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class GuardianInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextEntry::make('nama'),
                TextEntry::make('user.email')
                    ->label('Email')
                    ->placeholder('-'),
                TextEntry::make('tempat_lahir')
                    ->placeholder('-'),
                TextEntry::make('tanggal_lahir')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('gender')
                    ->formatStateUsing(fn ($state) => $state === 'male' ? 'Laki-laki' : 'Perempuan')
                    ->placeholder('-'),
                TextEntry::make('telepon')
                    ->placeholder('-'),
                TextEntry::make('alamat')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('pendidikan')
                    ->placeholder('-'),
                TextEntry::make('pekerjaan')
                    ->placeholder('-'),
                TextEntry::make('relation_type')
                    ->formatStateUsing(fn ($state) => Str::title(Str::replace('_', ' ', $state)))
                    ->placeholder('-'),
                IconEntry::make('is_alive')
                    ->boolean(),
                IconEntry::make('modifed_by_owner')
                    ->boolean(),
                TextEntry::make('telepon_verified_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
