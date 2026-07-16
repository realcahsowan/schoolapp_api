<?php

namespace App\Filament\AdminTahfidz\Resources\Murobbis\Tables;

use App\Models\Tahfidz\JournalPerformance;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MurobbisTable
{
    public static function configure(Table $table): Table
    {
        $tahunAjaran = app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
        $semester = app(\App\Settings\GeneralSettings::class)->semester;

        return $table
            // ->modifyQueryUsing(fn($query) => $query->where('tahun_ajaran', $tahunAjaran))
            // ->modifyQueryUsing(
            //     fn($query) => $query->with([
            //         'employee.dormitories' => fn($sub) => $sub->wherePivot('is_active', true),
            //     ])
            //     ->withCount('students')
            // )
            ->modifyQueryUsing(function ($query) use ($tahunAjaran, $semester) {
                if ($query->getModel() instanceof \App\Models\Murobbi) {
                    $query->where('tahun_ajaran', $tahunAjaran)
                          ->where('semester', $semester)
                          ->with([
                              'employee.dormitories' => fn($sub) => $sub->wherePivot('is_active', true),
                          ])->withCount('students');
                }
                //->withCount('students');
            })
            ->columns([
                TextColumn::make('nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('gender')
                    ->badge()
                    ->color(fn($state): string => $state === 'male' ? 'info' : 'danger')
                    ->formatStateUsing(fn($state) => $state === 'male' ? 'Laki-laki' : 'Perempuan'),
                TextColumn::make('students_count')
                    ->label('Jumlah Anggota')
                    ->counts('students'),
                TextColumn::make('employee.dormitories.name')
                    ->label('Asrama')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tahun_ajaran')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('semester')
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
            ->defaultSort('id', 'desc')
            ->filters([
                // \Filament\Tables\Filters\SelectFilter::make('tahun_ajaran')
                //     ->label('Tahun Ajaran')
                //     ->options(fn() => \App\Models\Murobbi::query()->distinct()->pluck('tahun_ajaran', 'tahun_ajaran')->toArray())
                //     ->default($tahunAjaran)
                //     ->placeholder('Semua'),
                // \Filament\Tables\Filters\SelectFilter::make('semester')
                //     ->label('Semester')
                //     ->options([
                //         '1' => 'Ganjil',
                //         '2' => 'Genap',
                //     ])
                //     ->default($semester)
                //     ->placeholder('Semua'),
                // \Filament\Tables\Filters\SelectFilter::make('dormitory')
                //     ->label('Asrama')
                //     ->options(fn () => \App\Models\Dormitory::query()->pluck('name', 'name')->toArray())
                //     ->query(function ($query, $data) {
                //         if (isset($data['value']) && $data['value'] !== null) {
                //             $query->whereHas('employee.dormitories', function ($q) use ($data) {
                //                 $q->where('name', $data['value'])->wherePivot('is_active', true);
                //             });
                //         }
                //     })
                //     ->placeholder('Semua'),
                \Filament\Tables\Filters\SelectFilter::make('gender')
                    ->label('Jenis Kelamin')
                    ->options([
                        'male' => 'Laki-laki',
                        'female' => 'Perempuan',
                    ])
                    ->placeholder('Semua'),
                \Filament\Tables\Filters\TernaryFilter::make('hasStudents')
                    ->label('Memiliki Anggota?')
                    ->queries(
                        fn($query) => $query->has('students'),
                        fn($query) => $query->doesntHave('students'),
                    ),

                \Filament\Tables\Filters\Filter::make('hasJournalSummaries')
                    // ->columnSpan(3)
                    // ->columns(2)
                    ->label('Ringkasan Mutabaah')
                    ->form([
                        \Filament\Schemas\Components\Section::make('Filter Jurnal')
                            ->schema([
                                \Filament\Forms\Components\DatePicker::make('date_start')->label('Tanggal Mulai'),
                                \Filament\Forms\Components\DatePicker::make('date_end')->label('Tanggal Akhir'),
                                \Filament\Forms\Components\Toggle::make('completed')->label('Sudah Selesai'),
                                \Filament\Forms\Components\Toggle::make('has_no_data')->label('Tidak Ada Data'),
                            ]),
                    ])
                    ->query(function ($query, array $data) {
                        if ($query->getModel() instanceof \App\Models\Murobbi) {
                            return $query->where(function ($q) use ($data) {
                                // If has_no_data is checked, we want murobbi records that DO NOT HAVE journal summaries in the given date range
                                if (! empty($data['has_no_data'])) {
                                    $q->whereDoesntHave('journalSummaries', function ($sub) use ($data) {
                                        if (! empty($data['date_start'])) {
                                            $sub->whereDate('tanggal', '>=', $data['date_start']);
                                        }
                                        if (! empty($data['date_end'])) {
                                            $sub->whereDate('tanggal', '<=', $data['date_end']);
                                        }
                                    });
                                } else {
                                    // Otherwise, filter murobbi that HAVE journal summaries matching criteria
                                    $q->whereHas('journalSummaries', function ($sub) use ($data) {
                                        if (! empty($data['date_start'])) {
                                            $sub->whereDate('tanggal', '>=', $data['date_start']);
                                        }
                                        if (! empty($data['date_end'])) {
                                            $sub->whereDate('tanggal', '<=', $data['date_end']);
                                        }
                                        if (array_key_exists('completed', $data) && $data['completed'] !== null) {
                                            $sub->where('completed', $data['completed'] ? 1 : 0);
                                        }
                                    });
                                }
                            });
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('performance')
                    ->label('Performance')
                    ->icon('heroicon-o-chart-bar')
                    ->modalHeading(fn($record) => "Performance: {$record->nama}")
                    ->modalWidth('xl')
                    ->modalContent(fn($record) => view('filament.admin-tahfidz.components.murobbi-performance', [
                        'murobbi' => $record,
                        'semesterly' => JournalPerformance::query()
                            ->where('murobbi_id', $record->id)
                            ->where('jenis_periode', 'semesterly')
                            ->orderByDesc('angka_periode')
                            ->get(),
                        'monthly' => JournalPerformance::query()
                            ->where('murobbi_id', $record->id)
                            ->where('jenis_periode', 'monthly')
                            ->orderByDesc('angka_periode')
                            ->get(),
                        'weekly' => JournalPerformance::query()
                            ->where('murobbi_id', $record->id)
                            ->where('jenis_periode', 'weekly')
                            ->orderByDesc('angka_periode')
                            ->get(),
                    ]))
                    ->modalFooterActions([]),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
