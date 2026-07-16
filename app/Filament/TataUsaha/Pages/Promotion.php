<?php

namespace App\Filament\TataUsaha\Pages;

use App\Models\Classroom;
use App\Models\PromotionBatch;
use App\Models\School;
use App\Settings\GeneralSettings;
use App\Jobs\Tahfidz\PromoteClassroomsJob;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class Promotion extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Proses Kenaikan Kelas';

    protected static string|UnitEnum|null $navigationGroup = 'Akademik';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowUpTray;

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.tata-usaha.pages.promotion';

    public function getActiveBatch(): ?PromotionBatch
    {
        $tenant = Filament::getTenant();
        $currentYear = app(GeneralSettings::class)->tahun_ajaran;

        $batch = PromotionBatch::where('tahun_ajaran', $currentYear)
            ->where('school_id', $tenant?->id)
            ->where('completed', false)
            ->first();

        if (! $batch) {
            return null;
        }

        $unpromotedCount = Classroom::where('school_id', $tenant?->id)
            ->where('tahun_ajaran', $batch->tahun_ajaran_asal)
            ->where('is_promoted', false)
            ->count();

        if ($unpromotedCount === 0) {
            $batch->update(['completed' => true]);

            return null;
        }

        return $batch;
    }

    protected function getViewData(): array
    {
        $batch = $this->getActiveBatch();
        $currentYear = app(GeneralSettings::class)->tahun_ajaran;
        $prevYear = $this->getPreviousAcademicYear($currentYear);
        $tenant = Filament::getTenant();

        $totalCount = Classroom::where('school_id', $tenant?->id)
            ->where('tahun_ajaran', $prevYear)
            ->count();

        $startedBatchId = session('batch_started');

        if ($startedBatchId) {
            $startedBatch = PromotionBatch::find($startedBatchId);

            if ($startedBatch && $startedBatch->completed) {
                return [
                    'batch' => $startedBatch,
                    'totalCount' => $totalCount,
                ];
            }
        }

        return [
            'batch' => $batch && $startedBatchId === $batch->id ? $batch : null,
            'totalCount' => $totalCount,
        ];
    }

    public function table(Table $table): Table
    {
        $tenant = Filament::getTenant();
        $currentYear = app(GeneralSettings::class)->tahun_ajaran;
        $yearToUse = $this->getPreviousAcademicYear($currentYear);

        return $table
            ->query(
                Classroom::query()
                    ->where('school_id', $tenant?->id)
                    ->where('tahun_ajaran', $yearToUse)
                    ->where('is_promoted', false)
            )
            ->headerActions([
                Action::make('prosesSemua')
                    ->label('Proses Semua Kenaikan')
                    ->color('success')
                    ->icon('heroicon-o-arrow-up-on-square')
                    ->requiresConfirmation()
                    ->modalHeading('Proses Kenaikan Semua Kelas')
                    ->modalDescription('Yakin akan menaikkan semua kelas yang belum diproses? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Proses Semua')
                    ->action(function () {
                        $tenant = Filament::getTenant();
                        $currentYear = app(GeneralSettings::class)->tahun_ajaran;
                        $yearToUse = $this->getPreviousAcademicYear($currentYear);

                        $classrooms = Classroom::where('school_id', $tenant?->id)
                            ->where('tahun_ajaran', $yearToUse)
                            ->where('is_promoted', false)
                            ->get();

                        if ($classrooms->isEmpty()) {
                            Notification::make()
                                ->info()
                                ->title('Tidak ada kelas yang perlu diproses.')
                                ->send();

                            return;
                        }

                        $firstClassroom = $classrooms->first();
                        $batch = PromotionBatch::firstOrCreate(
                            [
                                'tahun_ajaran' => $currentYear,
                                'school_id' => $tenant?->id,
                            ],
                            [
                                'completed' => false,
                                'classrooms' => [],
                                'tahun_ajaran_asal' => $firstClassroom->tahun_ajaran,
                            ]
                        );

                        PromoteClassroomsJob::dispatch(
                            $classrooms->pluck('id')->toArray(),
                            $batch->id,
                            $tenant?->id,
                        );

                        session()->flash('batch_started', $batch->id);

                        Notification::make()
                            ->success()
                            ->title('Proses promosi dimulai')
                            ->body(count($classrooms) . ' kelas akan diproses di latar belakang.')
                            ->send();
                    }),
            ])
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Kelas')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('level')
                    ->label('Tingkat')
                    ->sortable(),
                TextColumn::make('rombel')
                    ->label('Rombel'),
                TextColumn::make('students_count')
                    ->label('Jumlah Siswa')
                    ->counts('students'),
                IconColumn::make('is_promoted')
                    ->label('Sudah Diproses')
                    ->boolean(),
            ])
            ->actions([
                Action::make('promote')
                    ->label('Proses Kenaikan')
                    ->color('primary')
                    ->icon('heroicon-o-arrow-up')
                    ->requiresConfirmation()
                    ->visible(fn (Classroom $record) => !$record->is_promoted)
                    ->action(fn (Classroom $record) => $this->promote($record)),
            ])
            ->defaultSort('level', 'asc')
            ->defaultSort('nama', 'asc');
    }

    public function promote(Classroom $classroom): void
    {
        $tenant = Filament::getTenant();
        $currentYear = app(GeneralSettings::class)->tahun_ajaran;
        $schoolAliases = School::all()->pluck('alias', 'id');

        $batch = PromotionBatch::firstOrCreate(
            [
                'tahun_ajaran' => $currentYear,
                'school_id' => $tenant?->id,
            ],
            [
                'completed' => false,
                'classrooms' => [],
                'tahun_ajaran_asal' => $classroom->tahun_ajaran,
            ]
        );

        if ($classroom->is_promoted) {
            Notification::make()
                ->warning()
                ->title('Kelas sudah diproses.')
                ->send();

            return;
        }

        $classroom->load('students');
        $isGraduation = in_array((int) $classroom->level, [6, 9, 12]);

        DB::transaction(function () use ($classroom, $currentYear, $isGraduation, $schoolAliases) {
            $studentIds = $classroom->students->pluck('id');

            DB::table('classroom_student')
                ->whereIn('student_id', $studentIds)
                ->update(['is_active' => false]);

            if ($isGraduation) {
                DB::table('students')
                    ->whereIn('id', $studentIds)
                    ->update(['is_graduated' => true]);

                $classroom->update(['is_promoted' => true]);

                return;
            }

            $newLevel = $classroom->level === 'idad' ? 10 : (int) $classroom->level + 1;
            $newNama = $newLevel . $classroom->rombel;

            $newClassroom = Classroom::create([
                'nama' => $newNama,
                'level' => (string) $newLevel,
                'rombel' => $classroom->rombel,
                'alias' => $newNama . '-' . Arr::get($schoolAliases, $classroom->school_id),
                'employee_id' => $classroom->employee_id,
                'school_id' => $classroom->school_id,
                'tingkat_id' => $newLevel + 2,
                'jurusan_id' => $classroom->jurusan_id,
                'kurikulum_id' => $classroom->kurikulum_id,
                'tahun_ajaran' => $currentYear,
            ]);

            $pivotData = [];
            $riwayatUpdates = [];

            foreach ($classroom->students as $student) {
                $pivotData[] = [
                    'student_id' => $student->id,
                    'classroom_id' => $newClassroom->id,
                    'is_active' => true,
                ];

                $riwayat = is_array($student->riwayat_kelas) ? $student->riwayat_kelas : [];
                $riwayat[] = [
                    'classroom_id' => $newClassroom->id,
                    'tahun_ajaran' => $currentYear,
                ];
                $riwayat[] = [
                    'classroom_id' => $classroom->id,
                    'tahun_ajaran' => $classroom->tahun_ajaran,
                ];

                $riwayatUpdates[] = [
                    'id' => $student->id,
                    'riwayat_kelas' => json_encode($riwayat),
                ];
            }

            DB::table('students')->whereIn('id', $studentIds)->update(['classroom_id' => $newClassroom->id]);

            foreach ($riwayatUpdates as $update) {
                DB::table('students')->where('id', $update['id'])->update(['riwayat_kelas' => $update['riwayat_kelas']]);
            }

            DB::table('classroom_student')->insert($pivotData);

            $classroom->update(['is_promoted' => true]);
        });

        if ($isGraduation) {
            Notification::make()
                ->success()
                ->title('Siswa dari kelas ' . $classroom->nama . ' ditandai sebagai lulusan.')
                ->send();
        } else {
            Notification::make()
                ->success()
                ->title('Kelas ' . $classroom->nama . ' berhasil dinaikkan.')
                ->send();
        }

        $updatedClassrooms = $batch->classrooms;
        $updatedClassrooms[] = $classroom->id;
        $updatedClassrooms = array_unique($updatedClassrooms);
        $updateData = ['classrooms' => $updatedClassrooms];

        $prevClassroomIds = Classroom::where('school_id', $tenant?->id)
            ->where('tahun_ajaran', $batch->tahun_ajaran_asal)
            ->pluck('id')
            ->toArray();

        if (empty(array_diff($prevClassroomIds, $updatedClassrooms))) {
            $updateData['completed'] = true;
        }

        $batch->update($updateData);
    }

    private function getPreviousAcademicYear(string $current): string
    {
        [$start, $end] = explode('-', $current);

        return ($start - 1) . '-' . ($end - 1);
    }
}
