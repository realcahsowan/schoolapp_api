<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMurobbiFromPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-murobbi-from-positions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Murobbi from Positions for employees who have "Murobbi" position and no Murobbi record.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating Murobbi from active Murobbi positions...');

        $employees = \App\Models\Employee::whereHas('positions', function ($q) {
            $q->where('nama', 'Murobbi')->where('active', true);
        })->whereDoesntHave('murobbis')->get();

        $positionsToCreate = collect();
        foreach ($employees as $employee) {
            $positions = $employee->positions->where('nama', 'Murobbi')->where('active', true);
            foreach ($positions as $pos) {
                $positionsToCreate->push(['employee' => $employee, 'position' => $pos]);
            }
        }

        $count = $positionsToCreate->count();
        if ($count === 0) {
            $this->info('No eligible Murobbi positions found.');

            return 0;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $settings = app(\App\Settings\GeneralSettings::class);

        foreach ($positionsToCreate as $data) {
            $employee = $data['employee'];
            $position = $data['position'];

            \App\Models\Murobbi::firstOrCreate([
                'school_id' => $position->school_id,
                'employee_id' => $employee->id,
                'nama' => optional($employee->user)->name ?? $employee->nama,
                'gender' => $employee->gender,
                'tahun_ajaran' => $settings->tahun_ajaran,
                'semester' => $settings->semester,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nDone generating murobbi.");

        return 0;
    }
}
