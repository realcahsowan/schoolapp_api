<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Akun Pengguna')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Pengguna')
                            ->columnSpanFull(),
                        TextEntry::make('email')
                            ->label('Email'),
                        TextEntry::make('role')
                            ->label('Peran')
                            ->placeholder('-'),
                        // TextEntry::make('employee.nama')
                        //     ->label('Pegawai')
                        //     ->placeholder('-'),
                        // TextEntry::make('student.nama')
                        //     ->label('Siswa')
                        //     ->placeholder('-'),
                        // TextEntry::make('guardian.nama')
                        //     ->label('Wali')
                        //     ->placeholder('-'),
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
                        TextEntry::make('deleted_at')
                            ->label('Dihapus')
                            ->dateTime()
                            ->placeholder('-')
                            ->visible(fn (User $record): bool => $record->trashed()),
                    ])->columnSpanFull(),
            ]);
    }
}
