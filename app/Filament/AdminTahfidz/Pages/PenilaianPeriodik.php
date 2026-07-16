<?php

namespace App\Filament\AdminTahfidz\Pages;

use App\Models\Student;
use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class PenilaianPeriodik extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.admin-tahfidz.pages.penilaian-periodik';
    protected static ?string $title = 'Penilaian Periodik';
    protected static string|UnitEnum|null $navigationGroup = 'Monitoring Proses';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;

    public function table(Table $table): Table
    {
        $tahunAjaran = app(GeneralSettings::class)->tahun_ajaran;
        $semester = app(GeneralSettings::class)->semester;
        return $table
            ->query(Student::query()->with([
                'classroom',
                'penilaianPeriodik',
                // 'penilaianPeriodik' => fn ($query) => $query->where('tahun_ajaran', $tahunAjaran)
                //     ->where('semester', $semester),
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('nama')->label('Nama Siswa')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('gender')->label('Jenis Kelamin')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => '-',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('classroom.nama')->label('Kelas'),
                Tables\Columns\TextColumn::make('jumlah_penilaian')->label('Jumlah Penilaian')
                    ->getStateUsing(function ($record) {
                        return $record->penilaianPeriodik->count();
                    }),
                Tables\Columns\TextColumn::make('rerata')->label('Skor')
                    ->getStateUsing(function ($record) {
                        $avg = $record->penilaianPeriodik->avg('score');
                        if ($avg > 0) {
                            return number_format($avg, 2);
                        }
                        return $avg ?? 0;
                    }),
                Tables\Columns\TextColumn::make('murobbi.nama')->label('Murobbi')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        // '' => 'Semua',
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ]),

                Tables\Filters\SelectFilter::make('murobbi_id')
                    ->label('Murobbi')
                    ->options(function () {
                        $tenantSchoolId = Filament::getTenant()->id;
                        return \App\Models\Murobbi::where('school_id', $tenantSchoolId)
                            ->pluck('nama', 'id')->toArray();
                    })
                    ->query(function ($query, $filter) {
                        $data = $filter->getState();
                        return $query->when($data['value'], fn ($q) => $q->whereHas('penilaianPeriodik', function ($q) use ($data) {
                            $q->where('murobbi_id', $data['value']);
                        }));
                    }),

                // Filter siswa dengan penilaian periodik kurang dari konfigurasi jumlahPelaksanaanPeriodik
Tables\Filters\TernaryFilter::make('incomplete_periodic')
    ->label('Incomplete Periodic')
    ->placeholder('Semua')
    ->trueLabel('Belum Lengkap')
    ->falseLabel('Sudah Lengkap')
    ->queries(
        true: function ($query) use ($tahunAjaran, $semester) {
            $jumlah = \App\Models\Tahfidz\Configuration::where('name', 'jumlahPelaksanaanPeriodik')
                ->where('school_id', Filament::getTenant()->id)
                ->value('payload');
            if (is_numeric($jumlah)) {
                return $query->has('penilaianPeriodik', '<', $jumlah);
            }
            return $query;
        },
        false: function ($query) use ($tahunAjaran, $semester) {
            $jumlah = \App\Models\Tahfidz\Configuration::where('name', 'jumlahPelaksanaanPeriodik')
                ->where('school_id', Filament::getTenant()->id)
                ->value('payload');
            if (is_numeric($jumlah)) {
                return $query->has('penilaianPeriodik', '>=', $jumlah);
            }
            return $query;
        }
    )
            ]);
    }
}
