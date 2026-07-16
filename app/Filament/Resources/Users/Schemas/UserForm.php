<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('name')
                    ->label('Nama Pengguna')
                    ->required(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                // DateTimePicker::make('email_verified_at')
                //     ->label('Email Terverifikasi'),
                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->required(),
                // TextInput::make('role')
                //     ->label('Peran'),
                // TextInput::make('employee_id')
                //     ->label('Pegawai'),
                // TextInput::make('student_id')
                //     ->label('Siswa'),
                // TextInput::make('guardian_id')
                //     ->label('Wali'),
            ]);
    }
}
