<?php

namespace App\Filament\AdminTahfidz\Pages;

use UnitEnum;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use App\Models\Student;
use App\Settings\GeneralSettings;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Arr;

class CapaianTargetMutabaah extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'Monitoring Proses';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocument;
    protected string $view = 'filament.admin-tahfidz.pages.capaian-target-mutabaah';

    public static function table(Table $table): Table
    {
        $school = filament()->getTenant();
        $ta = app(GeneralSettings::class)->tahun_ajaran;
        $semester = app(GeneralSettings::class)->semester;

        return $table
            ->query(
                Student::query()->whereHas('classrooms', fn($q) => $q->where('school_id', $school->id))
                    // ->whereHas('examinations', fn ($query) => $query->lockedForCurrentYearSemester())
                    ->with([
                        'journals',
                        'murobbis' => fn($query) => $query->wherePivot('is_active', true),
                    ])
            )
            ->filters([
                // Tables\Filters\SelectFilter::make('gender')
                //     ->options([
                //         'male' => 'Laki-laki',
                //         'female' => 'Perempuan',
                //     ])
                //     ->label('Gender'),
                // Tables\Filters\SelectFilter::make('dormitory_id')
                //     ->relationship('dormitories', 'name')
                //     ->label('Asrama'),
                Tables\Filters\Filter::make('capaian_target_harian')
                    ->columnSpanFull()
                    ->columns(4)
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now()->toDateString()),
                        \Filament\Forms\Components\Select::make('waktu')
                            ->label('Waktu')
                            ->options([
                                'pagi' => 'Pagi',
                                'sore' => 'Sore',
                            ]),
                        \Filament\Forms\Components\Select::make('kehadiran')
                            ->options([
                                'hadir' => 'Hadir',
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'alpa' => 'Alpa',
                            ])
                            ->label('Kehadiran'),
                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'tercapai' => 'Tercapai',
                                'tidak_tercapai' => 'Tidak Tercapai',
                                'tidak_setor' => 'Tidak Setor',
                                'no_data' => 'Tidak Ada Data',
                            ])
                            ->label('Status')
                            ->default('no_data'),
                        \Filament\Forms\Components\Select::make('gender')
                            ->label('Gender')
                            ->options([
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                            ]),
                        \Filament\Forms\Components\Select::make('dormitory_id')
                            ->label('Asrama')
                            ->options(fn() => \App\Models\Dormitory::pluck('name', 'id')->toArray()),
                    ])
                    ->query(function ($query, $data) {
                        if (!empty($data['gender'])) {
                            $query->where('gender', $data['gender']);
                        }
                        if (!empty($data['dormitory_id'])) {
                            $tahunAjaran = app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
                            $semester = app(\App\Settings\GeneralSettings::class)->semester;
                            $query->whereHas('dormitories', function ($q) use ($data, $tahunAjaran, $semester) {
                                $q->where('dormitories.id', $data['dormitory_id'])
                                  ->where('tahun_ajaran', $tahunAjaran)
                                  ->where('semester', $semester)
                                  ->where('is_active', 1);
                            });
                        }
                        if (!empty($data['status']) && $data['status'] == 'no_data') {
                            return $query->whereDoesntHave('journals', function ($q) use ($data) {
                                if (!empty($data['tanggal'])) {
                                    $q->whereDate('tanggal', $data['tanggal']);
                                }
                                if (!empty($data['waktu'])) {
                                    $q->where('waktu', $data['waktu']);
                                }
                            });
                        }

                        return $query->whereHas('journals', function ($q) use ($data) {
                            if (!empty($data['tanggal'])) {
                                $q->whereDate('tanggal', $data['tanggal']);
                            }
                            if (!empty($data['waktu'])) {
                                $q->where('waktu', $data['waktu']);
                            }
                            if (!empty($data['status'])) {
                                if ($data['status'] === 'tercapai') {
                                    $q->where('status', 'tercapai');
                                } elseif ($data['status'] === 'tidak_tercapai') {
                                    $q->where('status', 'tidak_tercapai');
                                } elseif ($data['status'] === 'tidak_setor') {
                                    $q->whereIn('status', ['tidak_setor', 'tidak_setoran']);
                                }
                            }
                        });
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters()
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('classroom.nama')
                    ->label('Kelas'),
                Tables\Columns\TextColumn::make('murobbis.nama')
                    ->label('Murobbi'),
                Tables\Columns\TextColumn::make('activeDormitory.name')
                    ->label('Asrama'),
                Tables\Columns\TextColumn::make('activeDormitory.pivot.room')
                    ->label('Kamar'),
            ])
            ->actions([
                \Filament\Actions\Action::make('detail')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => 'Detail Mutabaah ' . $record->nama)
                    ->modalContent(function ($record, $livewire) {
                        $filters = $livewire->tableFilters['capaian_target_harian'] ?? [];
                        $tanggal = Arr::get($filters, 'tanggal');
                        $waktu = Arr::get($filters, 'waktu');
                        return static::getJournalModalContent($record, $tanggal, $waktu);
                    }),
            ]);
    }

    protected static function getJournalModalContent($student, ?string $tanggal = null, ?string $waktu = null)
    {
        $query = $student->journals()->newQuery();
        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }
        if ($waktu) {
            $query->where('waktu', $waktu);
        }
        $journal = $query->first();
        return view('filament.admin-tahfidz.pages.partials.journal-detail-modal', [
            'journal' => $journal,
        ]);
    }
}
