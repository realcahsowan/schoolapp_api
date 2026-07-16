<?php

namespace App\Filament\AdminTahfidz\Pages;

use App\Models\Student;
use App\Models\Tahfidz\Configuration;
use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

class RekapMuwashalatulAyat extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?int $navigationSort = 13;

    protected static ?string $title = 'Rekap Muwashalatul Ayat';

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian Akhir Semester';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    protected string $view = 'filament.admin-tahfidz.pages.rekap-muwashalatul-ayat';

    public function table(Table $table): Table
    {
        $school = filament()->getTenant();
        $kkm = Configuration::where('school_id', $school->id)->where('name', 'kkm')->first()?->payload;
        $ta = app(GeneralSettings::class)->tahun_ajaran;
        $semester = app(GeneralSettings::class)->semester;

        return $table
            ->query(
                Student::query()->whereHas('classrooms', fn($q) => $q->where('school_id', $school->id))
                    ->with([
                        'rapor',
                        'murobbis' => fn($query) => $query->wherePivot('is_active', true),
                        'memberMuwashalatAyats.murobbi',
                    ])
            )
            ->filters([
                Tables\Filters\SelectFilter::make('classroom')
                    ->label('Kelas')
                    ->options(fn() => \App\Models\Classroom::currentYear()->where('school_id', $school->id)->pluck('nama', 'id')->sort())
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->whereHas('classrooms', fn($q) => $q->where('classrooms.id', $data['value']));
                        }
                    }),
                Tables\Filters\SelectFilter::make('murobbi')
                    ->label('Penguji')
                    ->searchable()
                    ->options(fn() => \App\Models\Murobbi::pluck('nama', 'id')->sort())
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->whereHas('memberMuwashalatAyats.murobbi', fn($q) => $q->where('id', $data['value']));
                        }
                    }),
                Tables\Filters\TernaryFilter::make('dibawah_kkm')
                    ->label('Nilai di bawah KKM')
                    ->trueLabel('Hanya yang di bawah KKM')
                    ->falseLabel('Semua')
                    ->queries(
                        true: fn($query) => $query->whereHas('muwashalat', fn($q) => $q->where('score', '<', $kkm)),
                        false: fn($query) => $query,
                    ),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('classroom.nama')
                    ->label('Kelas'),
                Tables\Columns\TextColumn::make('nama_penguji')
                    ->label('Penguji')
                    ->getStateUsing(fn($record) => $record->muwashalat?->murobbi?->nama ?: '-'),
                Tables\Columns\TextColumn::make('muwashalat_score')
                    ->getStateUsing(fn($record) => is_numeric($record->muwashalat?->score) ? number_format((float) $record->muwashalat->score, 2) : '-')
                    ->label('Nilai'),
            ]);
        //
    }
}
