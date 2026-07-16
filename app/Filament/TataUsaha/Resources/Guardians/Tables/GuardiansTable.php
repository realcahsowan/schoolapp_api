<?php

namespace App\Filament\TataUsaha\Resources\Guardians\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class GuardiansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('tempat_lahir')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tanggal_lahir')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gender')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('telepon')
                    ->searchable(),
                TextColumn::make('pekerjaan')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('email_terverifikasi')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->user->email_verified_at !== null),
                TextColumn::make('relation_type')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('relation_status')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_alive')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('email_non_school')
                    ->query(fn ($query) => $query->whereHas('user', fn ($query) => $query->whereNot('email', 'like', '%@milbos.sch')))
                    ->label('Non-@milbos.sch Email'),
                Filter::make('student_name')
                    ->label('Nama Siswa')
                    ->form([
                        TextInput::make('student_name')
                            ->placeholder('Cari nama siswa...'),
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['student_name'])) {
                            return $query;
                        }

                        return $query->whereHas('students', function ($q) use ($data) {
                            $q->where('nama', 'like', '%'.$data['student_name'].'%');
                        });
                    }),
                Filter::make('student_relation')
                    ->label('Relasi Siswa')
                    ->form([
                        \Filament\Forms\Components\Select::make('student_relation')
                            ->options([
                                'has' => 'Tertaut Siswa',
                                'doesntHave' => 'Tidak Tertaut Siswa',
                            ])
                            ->placeholder('Pilih relasi...'),
                    ])
                    ->query(function ($query, array $data) {
                        if (($data['student_relation'] ?? null) === 'has') {
                            return $query->whereHas('students');
                        } elseif (($data['student_relation'] ?? null) === 'doesntHave') {
                            return $query->doesntHave('students');
                        }

                        return $query;
                    }),

            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
