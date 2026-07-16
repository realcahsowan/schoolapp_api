<?php

namespace App\Filament\AdminTahfidz\Resources\Students\Pages;

use App\Exceptions\TahfidzException;
use App\Filament\AdminTahfidz\Resources\Students\RelationManagers;
use App\Filament\AdminTahfidz\Resources\Students\StudentResource;
use App\Models\Tahfidz\Examination;
use App\Models\Tahfidz\Rapor;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('RecalculateRapor')
                ->label('Selaraskan Rapor')
                ->color('primary')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $this->runSingleStudentRaporSync($record);
                }),
            Action::make('Customize')
                ->label('Customize')
                ->schema([
                    Forms\Components\TagsInput::make('juz_map')
                        ->label('Juz Map')
                        ->placeholder('Masukkan daftar juz...')
                        ->required()
                        ->default(function ($record) {
                            if (! $record) {
                                return [];
                            }

                            $tahunAjaran = $record->getAttribute('tahun_ajaran') ?? (app(\App\Settings\GeneralSettings::class)->tahun_ajaran);
                            $semester = $record->getAttribute('semester') ?? (app(\App\Settings\GeneralSettings::class)->semester);

                            $examinationJuzMap = $record->examinations()
                                ->where('tahun_ajaran', $tahunAjaran)
                                ->where('semester', $semester)
                                ->pluck('juz')
                                ->toArray();

                            if (! empty($examinationJuzMap)) {
                                return $examinationJuzMap;
                            }

                            return $record->rapors()
                                ->where('tahun_ajaran', $tahunAjaran)
                                ->where('semester', $semester)
                                ->latest('id')
                                ->first()?->pas_juz_map ?? [];
                        }),
                ])
                ->action(function (array $data, $record) {
                    try {
                        $this->handleCustomize($data, $record);
                    } catch (TahfidzException $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal menyimpan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }

                    //     // Ambil rapor aktif siswa
                    //     $rapor = Rapor::firstOrCreate([
                    //         'student_id' => $record->id,
                    //         'tahun_ajaran' => $record->getAttribute('tahun_ajaran') ?? (app(\App\Settings\GeneralSettings::class)->tahun_ajaran),
                    //         'semester' => $record->getAttribute('semester') ?? (app(\App\Settings\GeneralSettings::class)->semester),
                    //     ]);
                    //     $rapor->pas_juz_map = $data['juz_map'];
                    //     $rapor->pas_has_customized_juz = true;
                    //     $rapor->save();
                    //
                    //     // Hapus examination lama yang tidak ada di juz_map
                    //     Examination::where('student_id', $record->id)
                    //         ->whereNotIn('juz', $data['juz_map'])
                    //         ->delete();
                    //
                    //     // Tambahkan/Update examination sesuai juz_map
                    //     // Ambil penguji_id aktif (jika ada)
                    //     $tahunAjaran = $record->getAttribute('tahun_ajaran') ?? (app(\App\Settings\GeneralSettings::class)->tahun_ajaran);
                    //     $semester = $record->getAttribute('semester') ?? (app(\App\Settings\GeneralSettings::class)->semester);
                    //     $pengujiId = $record->pengujis()
                    //         ->wherePivot('tahun_ajaran', $tahunAjaran)
                    //         ->wherePivot('semester', $semester)
                    //         ->first()?->id;
                    //     // Cek jika tidak ada penguji untuk tahun_ajaran dan semester sekarang
                    //     if (is_null($pengujiId)) {
                    //         // \Filament\Notifications\Notification::make()
                    //         //     ->title('Tidak ada penguji untuk tahun ajaran dan semester ini')
                    //         //     ->danger()
                    //         //     ->send();
                    //         throw new TahfidzException(
                    //             'Siswa belum ditauatkan dengan penguji untuk semester ini.'
                    //         );
                    //     }
                    //     // Note: wherePivot avoids ambiguity with tahfidz__penguji_student.tahun_ajaran
                    //     foreach ($data['juz_map'] as $juz) {
                    //         Examination::firstOrCreate([
                    //             'student_id' => $record->id,
                    //             'juz' => $juz,
                    //             'tahun_ajaran' => $rapor->tahun_ajaran,
                    //             'semester' => $rapor->semester,
                    //             'penguji_id' => $pengujiId,
                    //         ]);
                    //     }
                    //     \Filament\Notifications\Notification::make()
                    //         ->title('Kustomisasi juz berhasil disimpan')
                    //         ->success()
                    //         ->send();
                })
                ->color('primary')
                ->icon('heroicon-o-adjustments-horizontal'),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            RelationManagers\MurobbisRelationManager::class,
            RelationManagers\PengujisRelationManager::class,
            RelationManagers\PenilaianPeriodikRelationManager::class,
            RelationManagers\ExaminationsRelationManager::class,
            RelationManagers\JournalsRelationManager::class,
            RelationManagers\MemorizationSummariesRelationManager::class,
            RelationManagers\RaporsRelationManager::class,
        ];
    }

    private function runSingleStudentRaporSync($record): void
    {
        $tahunAjaran = $record->getAttribute('tahun_ajaran') ?? (app(\App\Settings\GeneralSettings::class)->tahun_ajaran);
        $semester = $record->getAttribute('semester') ?? (app(\App\Settings\GeneralSettings::class)->semester);
        $record->syncRaporsWithExaminations($tahunAjaran, $semester);
        \Filament\Notifications\Notification::make()
            ->title('Rapor recalculated successfully')
            ->success()
            ->send();
    }

    private function handleCustomize(array $data, $record): void
    {
        $tahunAjaran = $record->getAttribute('tahun_ajaran') ?? (app(\App\Settings\GeneralSettings::class)->tahun_ajaran);
        $semester = $record->getAttribute('semester') ?? (app(\App\Settings\GeneralSettings::class)->semester);

        // Ambil penguji_id aktif (jika ada)
        $pengujiId = $record->pengujis()
            ->wherePivot('tahun_ajaran', $tahunAjaran)
            ->wherePivot('semester', $semester)
            ->first()?->id;
        if (is_null($pengujiId)) {
            throw new \App\Exceptions\TahfidzException(
                'Siswa belum ditauatkan dengan penguji untuk semester ini.'
            );
        }

        // Ambil rapor aktif siswa
        $rapor = \App\Models\Tahfidz\Rapor::firstOrCreate([
            'student_id' => $record->id,
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semester,
        ]);
        $rapor->pas_juz_map = $data['juz_map'];
        $rapor->total_juz_pas = count($data['juz_map'] ?? []);
        $rapor->pas_has_customized_juz = true;
        $rapor->save();

        // Hapus examination lama yang tidak ada di juz_map
        \App\Models\Tahfidz\Examination::where('student_id', $record->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->whereNotIn('juz', $data['juz_map'])
            ->delete();

        // Tambahkan/Update examination sesuai juz_map
        foreach ($data['juz_map'] as $juz) {
            \App\Models\Tahfidz\Examination::firstOrCreate([
                'student_id' => $record->id,
                'penguji_id' => $pengujiId,
                'juz' => $juz,
                'tahun_ajaran' => $rapor->tahun_ajaran,
                'semester' => $rapor->semester,
                'school_id' => $record->classroom->school_id,
                'periode' => 'pas',
            ]);
        }
        \Filament\Notifications\Notification::make()
            ->title('Kustomisasi juz berhasil disimpan')
            ->success()
            ->send();
    }
}
