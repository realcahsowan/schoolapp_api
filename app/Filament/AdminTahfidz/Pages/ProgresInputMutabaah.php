<?php

namespace App\Filament\AdminTahfidz\Pages;

use App\Models\Murobbi;
use App\Models\Tahfidz\JournalSummary;
use App\Settings\GeneralSettings;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProgresInputMutabaah extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.admin-tahfidz.pages.progres-input-mutabaah';

    // protected static ?string $title = 'Progres Input Mutabaah';
    protected static ?string $title = 'Progres Mutabaah Hari Ini';

    protected static string|UnitEnum|null $navigationGroup = 'Monitoring Proses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ChartBar;

    public static function table(Table $table): Table
    {
        $tenantId = Filament::getTenant()?->id;
        $general = app(GeneralSettings::class);
        $defaultTahun = $general?->tahun_ajaran;
        $defaultSemester = $general?->semester;
        $today = Carbon::today()->toDateString();

        return $table
            ->query(
                Murobbi::query()
                    ->where('school_id', $tenantId)
                    ->whereHas('journalSummaries', fn($q) => $q->where('tanggal', $today))
                    // ->whereDoesntHave('journalSummaries', fn($q) => $q->where('tanggal', $today))
                    ->with([
                        'employee.dormitories' => fn($q) => $q->wherePivot('is_active', 1),
                        'journalSummaries' => fn($q) => $q->currentYearSemester()->with('kalender'),
                    ])
                // ->when($tenantId, fn(Builder $q) => $q->whereHas('murobbi', fn($qq) => $qq->where('school_id', $tenantId)))
                // ->orderBy('tanggal', 'desc')
            )
            ->columns([
                // TextColumn::make('id'),
                TextColumn::make('nama')->label('Nama Murobbi')->sortable()->searchable(),
                TextColumn::make('gender')->label('Gender Murobbi')
                    ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('kalender.tanggal')->label('Kalender')->date('d M Y')->sortable(),
                TextColumn::make('employee.dormitories.name')->label('Asrama')->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('employee.dormitories.pivot.room')->label('Kamar')->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('latestJournalSummary.tahun_ajaran')->label('Tahun Ajaran')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('latestJournalSummary.semester')->label('Semester')->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('latestJournalSummary.tanggal')
                    ->label('Tanggal Terakhir')->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('latestJournalSummary.target')->label('Target')->sortable(),
                TextColumn::make('latestJournalSummary.terisi')->label('Terisi')->sortable(),
                IconColumn::make('latestJournalSummary.completed')->label('Completed')->boolean(),
                IconColumn::make('latestJournalSummary.hp_only')->label('HP Only')->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // SelectFilter::make('tahun_ajaran')
                //     ->label('Tahun Ajaran')
                //     ->options(fn() => JournalSummary::query()
                //         ->distinct('tahun_ajaran')
                //         ->pluck('tahun_ajaran', 'tahun_ajaran')
                //         ->toArray())
                //     ->default($defaultTahun),
                // SelectFilter::make('semester')
                //     ->label('Semester')
                //     ->options([
                //         1 => '1',
                //         2 => '2',
                //     ])
                //     ->default($defaultSemester),
                // SelectFilter::make('murobbi_id')
                //     ->label('Murobbi')
                //     ->options(function () {
                //         $tenantId = Filament::getTenant()?->id;
                //         return Murobbi::when($tenantId, fn($q) => $q->where('school_id', $tenantId))
                //             ->orderBy('nama')
                //             ->pluck('nama', 'id')
                //             ->toArray();
                //     })
                //     ->searchable(),
                SelectFilter::make('gender')
                    ->label('Gender Murobbi')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ]),
                SelectFilter::make('completed')
                    ->label('Completed')
                    ->options([
                        1 => 'Completed',
                        0 => 'Not completed',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === null || $value === '') {
                            return $query;
                        }

                        return $query->whereHas('latestJournalSummary', function (Builder $journalQuery) use ($value): void {
                            $journalQuery->where('completed', (bool) $value);
                        });
                    }),
                /*
                Filter::make('tanggal')
                    ->label('Tanggal')
                    ->schema([
                        DatePicker::make('tanggal')->label('Tanggal')->default($today),
                        // DatePicker::make('from')->label('Dari')->default($today),
                        // DatePicker::make('until')->label('Sampai')->default($today),
                        Toggle::make('has_data')->label('Ada data?')->default(false),
                        Toggle::make('completed')->label('Completed')->default(false),
                    ])
                    ->columnSpanFull()
                    // ->columnSpan(2)
                    // ->columns(2)
                    // ->default(['from' => $today, 'until' => $today])
                    ->query(function (Builder $query, array $data) use ($today) {
                        $tanggal = $data['tanggal'] ?? $today;
                        $hasData = $data['has_data'] ?? true;
                        $completed = $data['completed'] ?? true;

                        if ($hasData) {
                            $query->whereHas('journalSummaries', function ($q) use ($tanggal, $completed) {
                                $q->whereDate('tanggal', $tanggal);

                                if ($completed) {
                                    $q->where('completed', true);
                                } else {
                                    $q->where('completed', false);
                                }
                            });
                        } else {
                            $query->whereDoesntHave('journalSummaries', function ($q) use ($tanggal) {
                                $q->whereDate('tanggal', $tanggal);
                            });
                        }

                        // $from = $data['from'] ?? null;
                        // $until = $data['until'] ?? null;
                        // if ($from && $until) {
                        //     if ($hasData) {
                        //         $query->whereHas('journalSummaries', function ($q) use ($from, $until) {
                        //             $q->whereDate('tanggal', '>=', $from)
                        //               ->whereDate('tanggal', '<=', $until);
                        //         });
                        //     } else {
                        //         $query->whereDoesntHave('journalSummaries', function ($q) use ($from, $until) {
                        //             $q->whereDate('tanggal', '>=', $from)
                        //               ->whereDate('tanggal', '<=', $until);
                        //         });
                        //     }
                        // } elseif ($from) {
                        //     if ($hasData) {
                        //         $query->whereHas('journalSummaries', function ($q) use ($from) {
                        //             $q->whereDate('tanggal', '>=', $from);
                        //         });
                        //     } else {
                        //         $query->whereDoesntHave('journalSummaries', function ($q) use ($from) {
                        //             $q->whereDate('tanggal', '>=', $from);
                        //         });
                        //     }
                        // } elseif ($until) {
                        //     if ($hasData) {
                        //         $query->whereHas('journalSummaries', function ($q) use ($until) {
                        //             $q->whereDate('tanggal', '<=', $until);
                        //         });
                        //     } else {
                        //         $query->whereDoesntHave('journalSummaries', function ($q) use ($until) {
                        //             $q->whereDate('tanggal', '<=', $until);
                        //         });
                        //     }
                        // }
                        return $query;
                    })
                    ->indicateUsing(function (?array $state): ?string {
                        if (! $state) {
                            return null;
                        }
                        $tanggal = $state['tanggal'] ?? null;
                        if ($tanggal) {
                            return "Tanggal: {$tanggal}";
                        }
                        // $from = $state['from'] ?? null;
                        // $until = $state['until'] ?? null;
                        // if ($from && $until) {
                        //     return "Tanggal: {$from} — {$until}";
                        // }
                        // if ($from) {
                        //     return "Dari: {$from}";
                        // }
                        // if ($until) {
                        //     return "Sampai: {$until}";
                        // }
                        return null;
                    }),
                */

            ]/* , layout: FiltersLayout::AboveContent */);
    }

    // set default table sort: tanggal desc
    protected function getDefaultTableSort(): ?array
    {
        return ['tanggal' => 'desc'];
    }

    protected function getTableActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat Mutabaah')
                ->modalHeading('Detail Mutabaah')
                ->modalContent(fn($record) => view('filament.admin-tahfidz.components.journal-detail-modal', [
                    'journals' => \App\Models\Tahfidz\Journal::query()
                        ->where('murobbi_id', $record->id)
                        ->where('tanggal', $record->latestJournalSummary?->tanggal)
                        ->get(),
                    'tanggal' => $record->latestJournalSummary?->tanggal,
                ])),
            // Tables\Actions\EditAction::make(),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        $tenantId = Filament::getTenant()?->id;
        $today = Carbon::today()->toDateString();

        $murobbis = Murobbi::when($tenantId, fn($q) => $q->where('school_id', $tenantId))
            ->whereDoesntHave('journalSummaries', fn($q) => $q->whereDate('tanggal', $today))
            ->orderBy('nama')
            ->get();

        $maleMurobbis = $murobbis->where('gender', 'male');
        $femaleMurobbis = $murobbis->where('gender', 'female');

        // render blade view for modal content
        // pass the View instance (do NOT ->render()) so modalContent receives a View
        $view = view('filament.admin-tahfidz.components.missing-murobbis', [
            'maleMurobbis' => $maleMurobbis,
            'femaleMurobbis' => $femaleMurobbis,
        ]);

        return [
            \Filament\Actions\Action::make('missing_murobbis')
                ->label('Murobbi Belum Input Hari Ini')
                ->icon('heroicon-o-exclamation-circle')
                ->modalHeading('Murobbi Belum Input Hari Ini')
                ->modalWidth('lg')
                ->color('warning')
                ->modalContent($view)
                // remove default modal buttons (no "kirim" / submit button)
                ->modalActions([]),

            \Filament\Actions\Action::make('run_summary')
                ->label('Sinkronisasi Progres Input')
                ->icon('heroicon-o-bolt')
                ->color('primary')
                ->requiresConfirmation()
                ->modalDescription('Aksi ini dapat membebani server, proses mungkin membutuhkan waktu, mohon tunggu hingga selesai dan hindari penggunaan berulang.')
                ->action(function () {
                    $filters = $this->getTableFiltersForm()->getState();

                    // default jika filter tidak ada
                    $tahunAjaran = $filters['tahun_ajaran']['value'] ?? app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
                    $semester = $filters['semester']['value'] ?? app(\App\Settings\GeneralSettings::class)->semester;
                    $exitCode = \Artisan::call('journal:summary', [
                        '--tahun_ajaran' => $tahunAjaran,
                        '--semester' => $semester,
                    ]);
                    if ($exitCode === 0) {
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Sinkonisasi Berhasil')
                            ->body("Berhasil sinkonisasi input mutabaah untuk tahun ajaran {$tahunAjaran}, semester {$semester}.")
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('Sisnkonisasi Gagal')
                            ->body('Proses sinkronisasi input mutabaah gagal. Silakan hubungi vendor.')
                            ->send();
                    }
                }),
        ];
    }
}
