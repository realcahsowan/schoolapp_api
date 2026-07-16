<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Section::make([
                        TextEntry::make('nama'),
                        // TextEntry::make('nik'),
                        TextEntry::make('nip')
                            ->placeholder('-'),
                        TextEntry::make('tempat_lahir')
                            ->placeholder('-'),
                        TextEntry::make('tanggal_lahir')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('gender')
                            ->placeholder('-'),
                        TextEntry::make('alamat')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('telepon')
                            ->placeholder('-'),
                        // TextEntry::make('file_foto')
                        //     ->placeholder('-'),
                        // TextEntry::make('file_signature')
                        //     ->placeholder('-'),
                        // TextEntry::make('institution_id')
                        //     ->numeric()
                        //     ->placeholder('-'),
                        // TextEntry::make('pendidikan_terakhir')
                        //     ->placeholder('-'),
                        TextEntry::make('angkatan_stipi')
                            ->placeholder('-'),
                        TextEntry::make('nuptk')
                            ->placeholder('-'),
                        TextEntry::make('peg_id')
                            ->placeholder('-'),
                        TextEntry::make('golongan_darah')
                            ->placeholder('-'),
                        TextEntry::make('tanggal_mulai_bekerja')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('status_perkawinan')
                            ->placeholder('-'),
                        TextEntry::make('nama_ayah')
                            ->placeholder('-'),
                        TextEntry::make('nama_ibu')
                            ->placeholder('-'),
                        TextEntry::make('nama_pasangan')
                            ->placeholder('-'),
                        TextEntry::make('jumlah_anak')
                            ->numeric()
                            ->placeholder('-'),
                    ])->columns(3),
                    Section::make([
                        ImageEntry::make('file_signature')
                         ->defaultImageUrl(function ($record) {
                             return asset('storage/' . $record->file_signature);
                         })
                            ->formatStateUsing(fn ($state) => Storage::url($state))
                            ->placeholder('-'),

                    ])->grow(false)
                ])->from('md')->columnSpanFull()
            ]);
    }
}
