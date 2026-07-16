<?php

namespace App\Filament\AdminTahfidz\Resources\JournalPerformances\Pages;

use App\Filament\AdminTahfidz\Resources\JournalPerformances\JournalPerformanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas;

class ListJournalPerformances extends ListRecords
{
    protected static string $resource = JournalPerformanceResource::class;

    public function getTitle(): string
    {
        return 'Performa Murobbi';
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('generate-data-performa')
                ->label('Generate Data Performa')
                ->requiresConfirmation()
                ->modalDescription('Mohon jalankan dulu Sinkronisasi di halaman Progres Input Mutabaah!')
                ->schema([
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
                ->action(function (array $data): void {
                    $schoolId = \Filament\Facades\Filament::getTenant()->id;
                    $murobbis = \App\Models\Murobbi::where('school_id', $schoolId)->get();
                    foreach ($murobbis as $murobbi) {
                        dispatch(new \App\Jobs\Tahfidz\GenerateJournalPerformanceJob($murobbi->id, $data['periode'], $data['number']));
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Status Proses')
                        ->body('Proses ini berjalan di latar belakang, mungkin butuh beberapa saat hingga data lengkap. Mohon tunggu dan reload ulang halaman.')
                        ->success()
                        ->send();
                })
                ->modalHeading('Generate Data Performa')
                ->color('primary'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Pekanan' => Schemas\Components\Tabs\Tab::make('Pekanan')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('jenis_periode', 'weekly');
                }),
            'Bulanan' => Schemas\Components\Tabs\Tab::make('Bulanan')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('jenis_periode', 'monthly');
                }),
            'Semester' => Schemas\Components\Tabs\Tab::make('Semester')
                ->modifyQueryUsing(function ($query) {
                    return $query->where('jenis_periode', 'semesterly');
                }),
        ];
    }
}
