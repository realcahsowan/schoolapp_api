<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Employee;
use App\Models\Guardian;
use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Akun Pengguna')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Pengguna')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        TextInput::make('password')
                            ->label('Kata Sandi')
                            ->password()
                            ->required(),
                        // Select::make('role')
                        //     ->label('Peran')
                        //     ->options([
                        //         'admin' => 'Admin',
                        //         'superuser' => 'Super User',
                        //     ]),
                    ])->columnSpanFull(),
                // Section::make('Relasi')
                //     ->columns(3)
                //     ->schema([
                //         Select::make('employee_id')
                //             ->label('Pegawai')
                //             ->options(Employee::pluck('nama', 'id'))
                //             ->searchable()
                //             ->nullable(),
                //         Select::make('student_id')
                //             ->label('Siswa')
                //             ->options(Student::pluck('nama', 'id'))
                //             ->searchable()
                //             ->nullable(),
                //         Select::make('guardian_id')
                //             ->label('Wali')
                //             ->options(Guardian::pluck('nama', 'id'))
                //             ->searchable()
                //             ->nullable(),
                //     ]),
            ]);
    }
}
