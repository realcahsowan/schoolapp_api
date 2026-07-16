<?php

namespace App\Filament\AdminTahfidz\Resources\Students\Tables;

use App\Exceptions\TahfidzException;
use App\Models\Tahfidz\MemberMuwashalatAyat;
use App\Models\Tahfidz\Penguji;
use App\Settings\GeneralSettings;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ->modifyQueryUsing(fn(Builder $query) => $query->with([
            //     'examinations' => fn($subQuery) => $subQuery->lockedForCurrentYearSemester(),
            // ]))
            ->columns([
                TextColumn::make('nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nis')
                    ->label('NIS')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('classroom.nama')
                    ->label('Kelas'),
                // TextColumn::make('nisn')
                //     ->searchable(),
                // TextColumn::make('nik')
                //     ->searchable(),
                // TextColumn::make('tempat_lahir')
                //     ->searchable(),
                // TextColumn::make('tanggal_lahir')
                //     ->date()
                //     ->sortable(),
                TextColumn::make('gender')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => '-',
                    }),
                TextColumn::make('murobbis.nama')
                    ->label('Murobbi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('murobbis.pivot.program')
                    ->label('Program')
                    ->formatStateUsing(fn($state) => Str::of($state)->replace('-', ' ')->title()),
                // TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('classroom_id')
                    ->label('Kelas')
                    ->relationship(
                        'classroom',
                        'nama',
                        fn($query) => $query
                            ->where('school_id', Filament::getTenant()?->id)
                            ->currentYear()
                    )
                    ->hiddenOn([
                        \App\Filament\AdminTahfidz\Resources\Students\RelationManagers\MurobbisRelationManager::class,
                        \App\Filament\AdminTahfidz\Resources\Students\RelationManagers\PengujisRelationManager::class,
                    ]),
                // Filter by classroom level
                \Filament\Tables\Filters\SelectFilter::make('classroom.level')
                    ->label('Tingkat Kelas')
                    ->options(function () {
                        $school = Filament::getTenant();
                        $jenjang = $school?->jenjang ?? null;
                        $levels = [];
                        if ($jenjang && ($label = \App\Traits\SekolahTrait::getJenjangOptions()[$jenjang] ?? null)) {
                            foreach (\App\Traits\SekolahTrait::getKelasLevelOptions($jenjang) as $level) {
                                $levels[$level] = 'Kelas ' . $level;
                            }
                            if ($jenjang === 'atas') {
                                $levels['idad'] = "Kelas I'dad";
                            }
                        }

                        return $levels;
                    })
                    ->query(function ($query, $data) {
                        $value = $data['value'] ?? null;
                        if ($value) {
                            return $query->whereHas('classroom', function ($subQuery) use ($value) {
                                $subQuery->where('level', $value);
                            });
                        }

                        return $query;
                    }),
                \Filament\Tables\Filters\SelectFilter::make('has_murobbi')
                    ->label('Status Murobbi')
                    ->options([
                        'with' => 'Ada Murobbi',
                        'without' => 'Tanpa Murobbi',
                    ])
                    ->query(function ($query, $data) {
                        $value = $data['value'];
                        if ($value === 'with') {
                            return $query->whereHas('murobbis');
                        } elseif ($value === 'without') {
                            return $query->whereDoesntHave('murobbis');
                        }

                        return $query;
                    }),
                \Filament\Tables\Filters\SelectFilter::make('has_penguji')
                    ->label('Status Penguji')
                    ->options([
                        'with' => 'Ada Penguji',
                        'without' => 'Tanpa Penguji',
                    ])
                    ->query(function ($query, $data) {
                        $value = $data['value'];
                        if ($value === 'with') {
                            return $query->whereHas('pengujis');
                        } elseif ($value === 'without') {
                            return $query->whereDoesntHave('pengujis');
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('CustomizeJuz')
                        ->label('Customize Juz')
                        ->icon('heroicon-o-cog')
                        ->form([
                            Forms\Components\TagsInput::make('juz_map')
                                ->label('Juz Map')
                                ->placeholder('Masukkan Juz – contoh: 1, 2, 3')
                                ->suggestions(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30']),
                        ])
                        ->action(function ($data, $records) {
                            try {
                                static::handleCustomizeJuz($data, $records);
                                Notification::make()
                                    ->title('Students linked to Penguji')
                                    ->success()
                                    ->send();
                            } catch (TahfidzException $e) {
                                Notification::make()
                                    ->title($e->getMessage())
                                    ->color('danger')
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    // BulkAction: Customize Program, set program & category for selected students' rapor
                    BulkAction::make('CustomizeProgram')
                        ->label('Customize Program')
                        ->icon('heroicon-o-academic-cap')
                        ->form([
                            Forms\Components\Select::make('program')
                                ->label('Program')
                                ->options(static::getProgramOptions())
                                ->required(),
                            Forms\Components\Select::make('category')
                                ->label('Kategori')
                                ->options(static::getCategoryOptions())
                                ->required(),
                        ])
                        ->action(function ($data, $records) {
                            $tahunAjaran = app(GeneralSettings::class)->tahun_ajaran;
                            $semester = app(GeneralSettings::class)->semester;

                            $count = 0;
                            foreach ($records as $student) {
                                $rapor = $student->rapor()
                                    ->where('tahun_ajaran', $tahunAjaran)
                                    ->where('semester', $semester)
                                    ->first();
                                if ($rapor) {
                                    $rapor->program = $data['program'];
                                    $rapor->category = $data['category'];
                                    $rapor->save();
                                    $count++;
                                }
                                // Update pivot student-murobbi yang is_active=true
                                $murobbiPivots = $student->murobbis()->wherePivot('is_active', true)->get();
                                foreach ($murobbiPivots as $murobbi) {
                                    $murobbi->pivot->program = $data['program'];
                                    $murobbi->pivot->category = $data['category'];
                                    $murobbi->pivot->save();
                                }
                            }
                            Notification::make()
                                ->title('Program & Kategori diupdate untuk ' . $count . ' siswa.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('AssignPengujiPAS')
                        ->label('Tautkan Penguji PAS')
                        ->icon('heroicon-o-users')
                        ->schema([
                            Forms\Components\Select::make('penguji_id')
                                ->label('Penguji')
                                ->searchable()
                                ->relationship(
                                    name: 'pengujis',
                                    titleAttribute: 'nama',
                                    modifyQueryUsing: fn(Builder $query) => $query->where('school_id', filament()->getTenant()->id)
                                )
                                ->pivotData([
                                    'active' => true,
                                ]),
                        ])
                        ->action(function ($data, $records) {
                            try {
                                static::attachToPenguji($data['penguji_id'], $records);
                                Notification::make()
                                    ->title('Students linked to Penguji')
                                    ->success()
                                    ->send();
                            } catch (TahfidzException $e) {
                                Notification::make()
                                    ->title($e->getMessage())
                                    ->color('danger')
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('AssignPengujiMuwasatalAyat')
                        ->label('Tautkan Penguji Muwashalatul Ayat')
                        ->icon('heroicon-o-users')
                        ->form([
                            Forms\Components\Select::make('murobbi_id')
                                ->label('Murobbi Penguji')
                                ->searchable()
                                ->relationship(
                                    name: 'murobbis',
                                    titleAttribute: 'nama',
                                    modifyQueryUsing: fn(Builder $query) => $query->where('school_id', filament()->getTenant()->id)
                                )
                                ->pivotData([
                                    'active' => true,
                                ]),
                        ])
                        ->action(function ($data, $records) {
                            // dd($data, $records);
                            try {
                                static::storeMuwashalatAyatMembers($data['murobbi_id'], $records);
                                Notification::make()
                                    ->title('Muwashalatul Ayat Members Stored')
                                    ->success()
                                    ->send();
                            } catch (TahfidzException $e) {
                                Notification::make()
                                    ->title($e->getMessage())
                                    ->color('danger')
                                    ->danger()
                                    ->persistent()
                                    ->send();
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function handleCustomizeJuz($data, $records): void
    {
        $tahunAjaran = app(GeneralSettings::class)->tahun_ajaran;
        $semester = app(GeneralSettings::class)->semester;
        foreach ($records as $student) {
            $pengujiPivot = $student->pengujis()->wherePivot('tahun_ajaran', $tahunAjaran)
                ->wherePivot('semester', $semester)
                ->wherePivot('periode', 'pas')
                ->first();

            if (! $pengujiPivot) {
                throw new TahfidzException('Siswa belum ditautkan ke penguji');
            }

            $rapor = $student->rapor()
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester)
                ->first();

            $juzMap = $data['juz_map'] ?? [];
            if ($rapor) {
                $rapor->pas_juz_map = $juzMap;
                $rapor->total_juz_pas = count($juzMap);
                $rapor->pas_has_customized_juz = true;
                $rapor->save();
                // Logic tambah/hapus examination sesuai pas_juz_map
                $existingExaminations = $student->examinations()
                    ->where('tahun_ajaran', $tahunAjaran)
                    ->where('semester', $semester)
                    ->where('periode', 'pas')
                    ->get();

                // Hapus examination yang juz-nya tidak ada di pas_juz_map
                $existingExaminations->whereNotIn('juz', $juzMap)->each->delete();
                // Tambah examination yang belum ada untuk juz di pas_juz_map
                foreach ($juzMap as $juz) {
                    if (! $existingExaminations->contains('juz', $juz)) {
                        // Cari penguji_id dari table pivot penguji_student
                        $penguji_id = $pengujiPivot?->id;
                        if ($penguji_id) {
                            \App\Models\Tahfidz\Examination::create([
                                'student_id' => $student->id,
                                'penguji_id' => $penguji_id,
                                'tahun_ajaran' => $tahunAjaran,
                                'semester' => $semester,
                                'juz' => $juz,
                                'periode' => 'pas',
                                'school_id' => $student->classroom->school_id,
                            ]);
                        }
                    }
                }
            }
        }
        Notification::make()
            ->title('Juz map updated for ' . $records->count() . ' students. Examinations disinkronisasi.')
            ->success()
            ->send();
    }

    public static function attachToPenguji($pengujiId, $students)
    {
        $penguji = Penguji::find($pengujiId);
        if (is_null($penguji)) {
            throw new TahfidzException('Penguji tidak ditemukan.');
        }
        if ($students->filter(fn($student) => is_null($student->rapor))->count() > 0) {
            throw new TahfidzException('Can not attach students who does not have target or rapor');
        }

        $attachments = [];
        foreach ($students as $student) {
            \App\Models\Tahfidz\PengujiStudent::create([
                'penguji_id' => $penguji->id,
                'student_id' => $student->id,
                'tahun_ajaran' => app(GeneralSettings::class)->tahun_ajaran,
                'semester' => app(GeneralSettings::class)->semester,
                'periode' => 'pas',
            ]);
        }
    }

    // Get program options from configuration (name='programs')
    private static function getProgramOptions(): array
    {
        $school = Filament::getTenant();
        $configuration = \App\Models\Tahfidz\Configuration::where('school_id', $school->id)->where('name', 'programs')->first();
        if (is_null($configuration)) {
            return [];
        }

        return collect($configuration->payload ?? [])->where('active', true)->pluck('nama', 'slug')->toArray();
    }

    // Get category options from configuration (name='categories')
    private static function getCategoryOptions(): array
    {
        $school = Filament::getTenant();
        $configuration = \App\Models\Tahfidz\Configuration::where('school_id', $school->id)->where('name', 'categories')->first();
        if (is_null($configuration)) {
            return [];
        }

        return collect($configuration->payload ?? [])->pluck('nama', 'slug')->toArray();
    }

    public static function storeMuwashalatAyatMembers($murobbiId, $students)
    {
        $year = app(GeneralSettings::class)->tahun_ajaran;
        $semester = app(GeneralSettings::class)->semester;
        foreach ($students as $student) {
            MemberMuwashalatAyat::firstOrCreate([
                'tahun_ajaran' => $year,
                'semester' => $semester,
                'student_id' => $student->id,
                'murobbi_id' => $murobbiId,
            ]);
        }
    }
}
