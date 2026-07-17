<?php

namespace App\Filament\Resources\Guardians\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GuardianInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pribadi')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('nama')
                            ->label('Nama Wali')
                            ->columnSpanFull(),
                        TextEntry::make('user.email')
                            ->label('Email')
                            ->placeholder('-'),
                        TextEntry::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->placeholder('-'),
                        TextEntry::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('gender')
                            ->label('Jenis Kelamin')
                            ->formatStateUsing(fn($state) => $state === 'male' ? 'Laki-laki' : 'Perempuan')
                            ->placeholder('-'),
                        TextEntry::make('telepon')
                            ->label('Telepon')
                            ->placeholder('-'),
                        TextEntry::make('alamat')
                            ->label('Alamat Lengkap')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
                Section::make('Data Lainnya')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('relation_type')
                            ->label('Jenis Hubungan')
                            ->formatStateUsing(fn($state) => match($state) {
                                'ayah' => 'Ayah',
                                'ibu' => 'Ibu',
                                default => $state,
                            })
                            ->placeholder('-'),
                        TextEntry::make('relation_status')
                            ->label('Status Hubungan')
                            ->formatStateUsing(fn($state) => match($state) {
                                'kandung' => 'Kandung',
                                'tiri' => 'Tiri',
                                'angkat' => 'Angkat',
                                default => $state,
                            })
                            ->placeholder('-'),
                        TextEntry::make('pendidikan')
                            ->label('Pendidikan')
                            ->placeholder('-'),
                        TextEntry::make('pekerjaan')
                            ->label('Pekerjaan')
                            ->placeholder('-'),
                        IconEntry::make('is_alive')
                            ->label('Masih Hidup')
                            ->boolean(),
                    ])->columnSpanFull(),
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
                    ])->columnSpanFull(),
            ]);
    }
}
