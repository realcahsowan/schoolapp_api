<?php

namespace App\Filament\AdminTahfidz\Resources\Students\Pages;

use App\Filament\AdminTahfidz\Resources\Students\StudentResource;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                \Filament\Actions\Action::make('updatePeriodikRapor')
                    ->label('Selaraskan Nilai Periodik')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->action(function () {
                        $schoolId = \Filament\Facades\Filament::getTenant()?->id;
                        Artisan::call('tahfidz:update-periodic-rapor', [
                            'schoolId' => $schoolId,
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Update Periodik Rapor Berhasil')
                            ->body('Perintah update periodic_score telah diproses.')
                            ->send();
                    }),
                \Filament\Actions\Action::make('updateRaporData')
                    ->label('Sync Tanggal Rapor')
                    ->icon('heroicon-o-document-arrow-up')
                    ->requiresConfirmation()
                    ->action(function () {
                        $schoolId = \Filament\Facades\Filament::getTenant()?->id;

                        if (! $schoolId) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('School Context Not Found')
                                ->body('Tidak bisa menentukan sekolah aktif. Pastikan tenant sudah dipilih.')
                                ->send();

                            return;
                        }

                        Artisan::call('tahfidz:update-rapor-data', [
                            'school_id' => $schoolId,
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Sync Tanggal Rapor Berhasil')
                            ->body('Perintah sync tanggal rapor telah diproses.')
                            ->send();
                    }),
                \Filament\Actions\Action::make('generateMemorizationSummary')
                    ->label('Generate Memorization Summary')
                    ->icon('heroicon-o-document-plus')
                    ->form([
                        \Filament\Forms\Components\Select::make('periode')
                            ->label('Periode')
                            ->options([
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                                'semesterly' => 'Semesterly',
                            ])
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('number')
                            ->label('Number')
                            ->numeric()
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalDescription('Proses ini berjalan di latar belakang dan mungkin butuh waktu, mohon tunggu hingga selesai.')
                    ->action(function (array $data) {
                        $generalSettings = app(\App\Settings\GeneralSettings::class);
                        $tahunAjaran = $generalSettings->tahun_ajaran;
                        $semester = $generalSettings->semester;

                        $schoolId = \Filament\Facades\Filament::getTenant()?->id;
                        if (! $schoolId) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('School Context Not Found')
                                ->body('Tidak bisa menentukan sekolah aktif. Pastikan tenant sudah dipilih.')
                                ->send();

                            return;
                        }

                        $students = \App\Models\Student::whereHas('classroom', function ($q) use ($schoolId) {
                            $q->where('school_id', $schoolId);
                        })->get();

                        if ($students->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('No Students Found')
                                ->body('Tidak ada siswa ditemukan untuk sekolah ini.')
                                ->send();

                            return;
                        }

                        $dispatched = 0;
                        foreach ($students as $student) {
                            \App\Jobs\SummaryMemorizationByPeriod::dispatch(
                                $student,
                                $tahunAjaran,
                                $semester,
                                $data['periode'],
                                $data['number']
                            );
                            $dispatched++;
                        }

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Memorization Jobs Dispatched')
                            ->body("Berhasil mendispatch {$dispatched} job summary memorization untuk seluruh siswa di sekolah ini.")
                            ->send();
                    }),
            ])
                ->label('Aksi Cepat')
                ->button(),
        ];
    }
}
