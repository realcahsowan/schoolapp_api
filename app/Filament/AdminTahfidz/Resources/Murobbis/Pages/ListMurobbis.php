<?php

namespace App\Filament\AdminTahfidz\Resources\Murobbis\Pages;

use App\Filament\AdminTahfidz\Resources\Murobbis\MurobbiResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListMurobbis extends ListRecords
{
    protected static string $resource = MurobbiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
            \Filament\Actions\Action::make('generate-murobbi-data')
                ->label('Generate Murobbi Data')
                ->action(fn() => $this->generateMurobbiData())
                ->requiresConfirmation()
                ->color('primary'),
        ];
    }

    protected function generateMurobbiData(): void
    {
        $settings = app(\App\Settings\GeneralSettings::class);

        $tahunAjaran = $settings->tahun_ajaran;
        $semester = $settings->semester;
        $employees = \App\Models\Employee::whereHas('positions', function ($q) {
            $q->where('nama', 'Murobbi')
               ->where('school_id', Filament::getTenant()->id);
        })->get();

        foreach ($employees as $employee) {
            $position = $employee->positions()->where('nama', 'Murobbi')->first();
            if (!$position) {
                continue;
            }

            \App\Models\Murobbi::firstOrCreate([
                'employee_id' => $employee->id,
                'school_id' => $position->school_id,
                'nama' => $employee->nama,
                'gender' => $employee->gender,
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
            ]);
        }
    }
}
