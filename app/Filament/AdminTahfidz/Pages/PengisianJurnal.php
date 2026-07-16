<?php

namespace App\Filament\AdminTahfidz\Pages;

use App\Models\Tahfidz\Journal;
use App\Models\Murobbi;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use UnitEnum;

class PengisianJurnal extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.admin-tahfidz.pages.pengisian-jurnal';

    protected static ?string $title = 'Pengisian Jurnal';

    protected static string|UnitEnum|null $navigationGroup = 'Monitoring Proses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected function getTableHeaderActions(): array
    {
        $tenantId = Filament::getTenant()?->id;
        $today = Carbon::today()->toDateString();

        $murobbis = Murobbi::query()
            ->when($tenantId, fn (Builder $query) => $query->where('school_id', $tenantId))
            ->with([
                'journalSummaries' => fn (HasMany $query) => $query
                    ->currentYearSemester()
                    ->whereDate('tanggal', $today),
            ])
            ->orderBy('nama')
            ->get();

        $notCompleted = $murobbis->filter(function (Murobbi $murobbi): bool {
            $summary = $murobbi->journalSummaries->first();

            if (! $summary) {
                return false;
            }

            return (float) $summary->terisi < (float) $summary->target;
        });

        $notFound = $murobbis->filter(fn (Murobbi $murobbi): bool => $murobbi->journalSummaries->isEmpty());

        $message = $this->buildWarningMessage($notCompleted, $notFound, $today);

        return [
            Action::make('peringatan')
                ->label('Peringatan')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->modalHeading('Peringatan Pengisian Jurnal')
                ->modalWidth('4xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(view('filament.admin-tahfidz.components.journal-warning-modal', [
                    'message' => $message,
                    'tanggal' => $today,
                ])),
        ];
    }

    public function table(Table $table): Table
    {
        $tenantId = Filament::getTenant()?->id;
        $today = Carbon::today()->toDateString();

        return $table
            ->query(
                Murobbi::query()
                    ->when($tenantId, fn (Builder $query) => $query->where('school_id', $tenantId))
                    ->with([
                        'journalSummaries' => fn (HasMany $query) => $query
                            ->currentYearSemester()
                            ->whereDate('tanggal', $today),
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                        default => '-',
                    }),
                Tables\Columns\TextColumn::make('journal_summary_target')
                    ->label('Target')
                    ->getStateUsing(fn (Murobbi $record) => $record->journalSummaries->first()?->target ?? '-'),
                Tables\Columns\TextColumn::make('journal_summary_terisi')
                    ->label('Terisi')
                    ->getStateUsing(fn (Murobbi $record) => $record->journalSummaries->first()?->terisi ?? '-'),
                Tables\Columns\IconColumn::make('completed')
                    ->label('Completed')
                    ->boolean()
                    ->getStateUsing(function (Murobbi $record): bool {
                        $summary = $record->journalSummaries->first();

                        if (! $summary) {
                            return false;
                        }

                        return (string) $summary->target === (string) $summary->terisi;
                    }),
            ])
            ->filters([
                SelectFilter::make('gender')
                    ->label('Gender')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ])
                    ->placeholder('Semua'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'completed' => 'Completed',
                        'not_completed' => 'Not Completed',
                        'no_summary' => 'No Today Journal Summary',
                    ])
                    ->placeholder('Semua')
                    ->query(function (Builder $query, array $data) use ($today): Builder {
                        $value = $data['value'] ?? null;

                        if (! $value) {
                            return $query;
                        }

                        return match ($value) {
                            'completed' => $query->whereHas('journalSummaries', function (Builder $journalQuery) use ($today): void {
                                $journalQuery->currentYearSemester()
                                    ->whereDate('tanggal', $today)
                                    ->whereColumn('target', 'terisi');
                            }),
                            'not_completed' => $query->whereHas('journalSummaries', function (Builder $journalQuery) use ($today): void {
                                $journalQuery->currentYearSemester()
                                    ->whereDate('tanggal', $today)
                                    ->whereColumn('target', '!=', 'terisi');
                            }),
                            'no_summary' => $query->whereDoesntHave('journalSummaries', function (Builder $journalQuery) use ($today): void {
                                $journalQuery->currentYearSemester()
                                    ->whereDate('tanggal', $today);
                            }),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Action::make('ikhtisar')
                    ->label('Ikhtisar')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (Murobbi $record) => "Ikhtisar Jurnal: {$record->nama}")
                    ->modalWidth('3xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(function (Murobbi $record) use ($today) {
                        $journals = Journal::query()
                            ->currentYearSemester()
                            ->where('murobbi_id', $record->id)
                            ->whereDate('tanggal', $today)
                            ->with('student')
                            ->orderBy('waktu')
                            ->get();

                        return view('filament.admin-tahfidz.components.journal-ikhtisar-modal', [
                            'journals' => $journals,
                            'tanggal' => $today,
                        ]);
                    }),
            ])
            ->defaultSort('nama');
    }

    protected function buildWarningMessage(Collection $notCompleted, Collection $notFound, string $today): string
    {
        $blocks = [
            'Peringatan Pengisian Jurnal',
            'Tanggal: ' . Carbon::parse($today)->translatedFormat('l, d M Y'),
            "Belum Lengkap\n" . $this->formatGroupedMurobbis($notCompleted),
            "Belum Ditemukan\n" . $this->formatGroupedMurobbis($notFound),
        ];

        return trim(implode("\n\n", array_filter($blocks, fn (string $block) => trim($block) !== '')));
    }

    protected function formatGroupedMurobbis(Collection $murobbis): string
    {
        $genderGroups = $murobbis
            ->groupBy(fn (Murobbi $murobbi) => match ($murobbi->gender) {
                'male' => 'Laki-laki',
                'female' => 'Perempuan',
                default => 'Lainnya',
            });

        if ($genderGroups->isEmpty()) {
            return '-';
        }

        $sections = [];

        foreach (['Laki-laki', 'Perempuan', 'Lainnya'] as $genderLabel) {
            $items = $genderGroups->get($genderLabel, collect())->sortBy('nama')->values();

            if ($items->isEmpty()) {
                continue;
            }

            $sections[] = $genderLabel;

            foreach ($items as $murobbi) {
                $sections[] = '- ' . $murobbi->nama;
            }

            $sections[] = '';
        }

        return trim(implode("\n", $sections));
    }
}
