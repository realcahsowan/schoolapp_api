<?php

namespace App\Filament\AdminTahfidz\Pages;

use App\Models\classroom;
use App\Models\Student;
use App\Models\Tahfidz\Configuration;
use App\Settings\GeneralSettings;
use App\Traits\KelasTrait;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RekapHasilPas extends Page implements Tables\Contracts\HasTable
{
    use KelasTrait;
    use Tables\Concerns\InteractsWithTable;

    protected static ?int $navigationSort = 13;

    protected static ?string $title = 'Rekap Hasil PAS';

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian Akhir Semester';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    protected string $view = 'filament.admin-tahfidz.pages.rekap-hasil-pas';

    /**
     * Adds a page header action to sync Rapor with Examinations.
     */
    public function getHeaderActions(): array
    {
        return [
            Action::make('sync-rapor-examinations')
                ->label('Sync Examinations')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $school = filament()->getTenant();
                    $ta = app(GeneralSettings::class)->tahun_ajaran;
                    $semester = app(GeneralSettings::class)->semester;

                    $students = Student::query()
                        ->whereHas('classrooms', fn ($q) => $q->where('school_id', $school->id))
                        ->with([
                            'rapors' => function ($q) use ($ta, $semester) {
                                $q->where('tahun_ajaran', $ta)->where('semester', $semester);
                            },
                            'examinations' => function ($q) use ($ta, $semester) {
                                $q->where('tahun_ajaran', $ta)->where('semester', $semester);
                            },
                            'memberMuwashalatAyats' => function ($q) use ($ta, $semester) {
                                $q->where('tahun_ajaran', $ta)->where('semester', $semester);
                            },
                            'penilaianPeriodik' => function ($q) use ($ta, $semester) {
                                $q->where('tahun_ajaran', $ta)->where('semester', $semester);
                            },
                        ])
                        ->get();

                    foreach ($students as $student) {
                        $student->syncRaporsWithExaminations($ta, $semester);
                    }

                    Notification::make()
                        ->success()
                        ->title('Sync Completed')
                        ->body('Examinations synced successfully.')
                        ->send();
                }),
        ];
    }

    public static function table(Table $table): Table
    {
        $school = filament()->getTenant();
        $kkm = Configuration::where('school_id', $school->id)->where('name', 'kkm')->first()?->payload;
        $ta = app(GeneralSettings::class)->tahun_ajaran;
        $semester = app(GeneralSettings::class)->semester;

        return $table
            ->query(
                Student::query()->whereHas('classrooms', fn ($q) => $q->where('school_id', $school->id))
                    // ->whereHas('examinations', fn ($query) => $query->lockedForCurrentYearSemester())
                    ->with([
                        'rapor',
                        'pengujis' => fn ($query) => $query->where('tahfidz__pengujis.tahun_ajaran', $ta)
                            ->where('tahfidz__pengujis.semester', $semester),
                        // 'classrooms' => fn ($query) => $query->wherePivot('active', true),
                        'murobbis' => fn ($query) => $query->wherePivot('is_active', true),
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('classroom.nama')
                    ->label('Kelas'),
                Tables\Columns\TextColumn::make('rapor.total_juz_pas')
                    ->label('Target'),
                Tables\Columns\TextColumn::make('diuji')
                    ->state(fn ($record) => count($record->rapor->pas_juz_scores ?? [])),
                Tables\Columns\TextColumn::make('rapor.pas_score')
                    ->label('Nilai Akhir')
                    // ->formatStateUsing(fn ($state) => number_format($state, 2)),
                    ->color(fn ($state) => $state < $kkm ? 'danger' : 'success')
                    ->numeric(decimalPlaces: 2),
                Tables\Columns\IconColumn::make('rapor.pas_succeed')
                    ->boolean()
                    ->label('Tuntas'),
                Tables\Columns\TextColumn::make('murobbis.nama')
                    ->label('Murobbi')
                    ->toggleable(),
                // FIX THIS FOR OTHER YEAR OR SEMESTER
                Tables\Columns\TextColumn::make('pengujis.nama')
                    ->label('Penguji')
                    ->toggleable(),

            ])
            ->actions([
                \Filament\Actions\Action::make('lihat_ujian')
                    ->label('Lihat Ujian')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => \App\Filament\AdminTahfidz\Resources\Students\Pages\ViewStudent::getUrl(['record' => $record->id]).'?relation=2')
                    ->openUrlInNewTab(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('ketuntasan')
                    ->label('Tuntas')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('rapor', fn ($query) => $query->where('pas_succeed', true)),
                        false: fn (Builder $query) => $query->whereHas('rapor', fn ($query) => $query->where('pas_succeed', false)),
                        blank: fn (Builder $query) => $query,
                    ),
                Tables\Filters\TernaryFilter::make('kkm')
                    ->label('Mencapai KKM')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('rapor', fn ($query) => $query->where('pas_score', '>=', $kkm)),
                        false: fn (Builder $query) => $query->whereHas('rapor', fn ($query) => $query->where('pas_score', '<', $kkm)),
                        blank: fn (Builder $query) => $query,
                    ),
                Tables\Filters\TernaryFilter::make('pas_completed_juz_null')
                    ->label('PAS Kosong')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('rapor', fn ($query) => $query->whereNull('pas_completed_juz')),
                        false: fn (Builder $query) => $query->whereHas('rapor', fn ($query) => $query->whereNotNull('pas_completed_juz')),
                        blank: fn (Builder $query) => $query,
                    ),
                Tables\Filters\TernaryFilter::make('nulled')
                    ->label('Ada Nilai Nol')
                    ->queries(
                        true: fn (Builder $query) => $query->whereHas('examinations', fn ($query) => $query->lockedForCurrentYearSemester()->whereHas('mistakes', fn ($sq) => $sq->where('is_nulled', true))),
                        false: fn (Builder $query) => $query,
                        blank: fn (Builder $query) => $query,
                    ),
                Tables\Filters\SelectFilter::make('classroom')
                    ->label('Kelas')
                    ->options(self::getClassroomsOptions(filament()->getTenant()))
                    ->modifyQueryUsing(function (Builder $query, $state) {
                        if (! $state['value']) {
                            return $query;
                        }
                        $query->where('classroom_id', $state['value']);
                        // return $query->whereHas('classroom', fn ($query) => $query->where('classroom_id', $state['value']));
                    }),
                Tables\Filters\SelectFilter::make('gender')
                    ->options(['male' => 'Male', 'female' => 'Female'])
                    ->modifyQueryUsing(function (Builder $query, $state) {
                        if (! $state['value']) {
                            return $query;
                        }

                        return $query->where('gender', $state['value']);
                    }),
            ]);
    }
}
