<?php

namespace App\Filament\TataUsaha\Resources\Students\Tables;

use App\Models\Classroom;
use App\Settings\GeneralSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('dormitories'))
            ->columns([
                TextColumn::make('nama')
                    // ->getStateUsing(function ($record) {
                    //     // dd($record->dormitories->toArray());
                    //     return strtoupper($record->nama);
                    // })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('nik')
                //     ->searchable(),
                TextColumn::make('tempat_lahir')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tanggal_lahir')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gender')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'male' => 'primary',
                        'female' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('telepon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('anak_ke')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('jumlah_saudara')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('sekolah_asal')
                //     ->searchable(),
                // TextColumn::make('nomor_ijazah')
                //     ->searchable(),
                IconColumn::make('is_graduated')
                    ->boolean(),
                // IconColumn::make('is_beasiswa')
                //     ->boolean(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('has_siblings')
                    ->boolean(),
                // TextColumn::make('virtual_account')
                //     ->searchable(),
                // TextColumn::make('agama')
                //     ->searchable(),
                // TextColumn::make('file_foto')
                //     ->searchable(),
                // TextColumn::make('pendidikan')
                //     ->searchable(),

                // TextColumn::make('propinsi')
                //     ->searchable(),
                // TextColumn::make('kabupaten_kota')
                //     ->searchable(),
                // TextColumn::make('kecamatan')
                //     ->searchable(),
                // TextColumn::make('kelurahan')
                //     ->searchable(),
                // TextColumn::make('kodepos')
                //     ->searchable(),
                // TextColumn::make('tingkat_id')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('classroom_id')
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('classroom.nama')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable(),
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
                    ->label('Gender')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ]),
                SelectFilter::make('classroom_id')
                    ->label('Kelas')
                    ->options(function () {
                        $tahunAjaran = app(GeneralSettings::class)->tahun_ajaran;
                        $schoolId = Filament::getTenant()?->id;

                        return Classroom::where('tahun_ajaran', $tahunAjaran)
                            ->where('school_id', $schoolId)
                            ->pluck('nama', 'id')
                            ->toArray();
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('move_classroom')
                        ->label('Pindah Kelas')
                        ->icon('heroicon-o-arrow-right-on-rectangle')
                        ->schema([
                            \Filament\Forms\Components\Select::make('classroom_id')
                                ->label('Kelas Tujuan')
                                ->options(function () {
                                    $tahunAjaran = app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
                                    $schoolId = \Filament\Facades\Filament::getTenant()?->id;

                                    return \App\Models\Classroom::where('tahun_ajaran', $tahunAjaran)
                                        ->where('school_id', $schoolId)
                                        ->orderBy('nama')
                                        ->pluck('nama', 'id')
                                        ->toArray();
                                })
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $student) {
                                $student->update(['classroom_id' => $data['classroom_id']]);
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil memindahkan kelas siswa')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
