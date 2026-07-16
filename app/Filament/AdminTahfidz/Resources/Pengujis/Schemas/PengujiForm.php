<?php

namespace App\Filament\AdminTahfidz\Resources\Pengujis\Schemas;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;

class PengujiForm
{
    public static function configure(Schema $schema): Schema
    {
        $general = app(GeneralSettings::class);
        $tahunAjaran = $general->tahun_ajaran ?? '';
        $semester = $general->semester ?? 1;

        return $schema
            ->components([
                Hidden::make('school_id')
                    ->default(Filament::getTenant()?->id),
                // Employee select with relationship and filter jabatan
                Select::make('employee_id')
                    ->label('Pegawai Penguji')
                    ->relationship(
                        name: 'employee',
                        titleAttribute: 'nama',
                        modifyQueryUsing: fn ($query) =>
                            $query->whereHas('positions', fn ($q) =>
                                $q->where('nama', 'penguji')
                            )
                    )
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $employee = \App\Models\Employee::find($state);
                            $set('nama', $employee?->nama ?? '');
                            $set('gender', $employee?->gender ?? null);
                        } else {
                            $set('nama', '');
                            $set('gender', null);
                        }
                    }),
                Hidden::make('nama')
                    ->required(),
                Hidden::make('tahun_ajaran')
                    ->default($tahunAjaran),
                Hidden::make('semester')
                    ->default($semester),
                Hidden::make('gender')
                    ->required(),
            ])
            ->columns(1);
    }
}
