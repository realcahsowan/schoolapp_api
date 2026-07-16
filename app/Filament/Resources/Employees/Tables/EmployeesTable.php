<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Section;
use Filament\Notifications\Notification;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Pegawai')
                    ->searchable(),
                // TextColumn::make('nik')
                //     ->label('NIK')
                //     ->searchable(),
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable(),
                TextColumn::make('tempat_lahir')
                    ->label('Tempat Lahir')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'male' => 'primary',
                        'female' => 'primary',
                    })
                    ->formatStateUsing(fn($state) => Str::title($state)),
                TextColumn::make('telepon')
                    ->label('Telepon')
                    ->searchable(),
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
                SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ]),
                SelectFilter::make('signature_status')
                    ->label('Tanda Tangan')
                    ->options([
                        'exists' => 'Sudah ada tanda tangan',
                        'missing' => 'Belum ada tanda tangan',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'exists') {
                            $query->whereNotNull('file_signature')->where('file_signature', '!=', '');
                        } elseif ($data['value'] === 'missing') {
                            $query->where(function ($q) {
                                $q->whereNull('file_signature')->orWhere('file_signature', '');
                            });
                        }
                    }),
                // SelectFilter::make('position')
                //     ->label('Jabatan')
                //     ->options([
                //         'tata-usaha' => 'Tata Usaha',
                //         'Admin-tahfidz' => 'Admin Tahfidz',
                //         'Murobbi' => 'Murobbi',
                //     ])
                //     ->query(function ($query, $data) {
                //         $query->whereHas('positions', function ($q) use ($data) {
                //             $q->where('nama', $data['value']);
                //         });
                //     }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('addPosition')
                        ->label('Tambahkan Jabatan')
                        ->icon('heroicon-o-briefcase')
                        ->schema([
                            Section::make([
                                \Filament\Forms\Components\Select::make('nama')
                                    ->label('Nama Jabatan')
                                    ->options((new class {
                                        use \App\Traits\JabatanTrait;
                                    })->getJabatanOptions())
                                    ->required(),
                                \Filament\Forms\Components\Select::make('school_id')
                                    ->label('Sekolah')
                                    ->options(\App\Models\School::pluck('nama', 'id'))
                                    ->searchable()
                                    ->required(),
                                \Filament\Forms\Components\DatePicker::make('mulai')
                                    ->label('Tanggal Mulai')
                                    ->required(),
                                \Filament\Forms\Components\DatePicker::make('selesai')
                                    ->label('Tanggal Selesai')
                                    ->required(),
                            ])
                            ->columns(2),
                        ])
                        ->action(function (\Filament\Actions\BulkAction $action, array $data, \Illuminate\Support\Collection $records) {
                            foreach ($records as $employee) {
                                $employee->positions()->create([
                                    'nama' => $data['nama'],
                                    'school_id' => $data['school_id'],
                                    'mulai' => $data['mulai'],
                                    'selesai' => $data['selesai'],
                                    'is_active' => true,
                                ]);
                                // Ensure school_user entry for the user exists
                                $user = \App\Models\User::where('employee_id', $employee->id)->first();
                                if ($user && !$user->schools()->where('school_id', $data['school_id'])->exists()) {
                                    $user->schools()->attach($data['school_id']);
                                }
                            }
                            Notification::make()
                                ->title('Jabatan berhasil ditambahkan ke pegawai terpilih dan relasi diatur di school_user.')
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('assignDormitory')
                        ->label('Tautkan Kamar')
                        ->icon('heroicon-o-home')
                        ->schema([
                            Section::make([
                                \Filament\Forms\Components\Select::make('dormitory_id')
                                    ->label('Asrama')
                                    ->options(\App\Models\Dormitory::pluck('name', 'id')->toArray())
                                    ->required(),
                                \Filament\Forms\Components\TextInput::make('room')
                                    ->label('No. Kamar')
                                    ->required(),
                            ])->columns(2),
                        ])
                        ->action(function (\Filament\Actions\BulkAction $action, array $data, \Illuminate\Support\Collection $records) {
                            foreach ($records as $employee) {
                                // Tautkan kamar baru/set aktif kamar baru
                                // Gunakan updateOrInsert agar tidak terjadi duplicate key error
                                \DB::table('dormitory_employee')
                                    ->where('employee_id', $employee->id)
                                    ->update([
                                        'is_active' => false,
                                    ]);

                                \DB::table('dormitory_employee')->updateOrInsert(
                                    [
                                        'employee_id' => $employee->id,
                                        'dormitory_id' => $data['dormitory_id'],
                                        'room' => $data['room'],
                                    ],
                                    [
                                        'is_active' => true,
                                    ]
                                );
                            }
                            Notification::make()
                                ->title('Kamar berhasil ditautkan ke pegawai terpilih.')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
