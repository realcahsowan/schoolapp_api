<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Jobs\SummaryMemorizationByPeriod;

class CreateMemorizationSummaries extends Command
{
    protected $signature = 'memorization:create-summaries
        {tahunAjaran? : Tahun ajaran}
        {semester? : Semester}
        {periodeType? : weekly|monthly|semesterly}
        {periodNumber? : Nomor minggu/bulan/semester (opsional)}';

    protected $description = 'Create memorization summaries for all students';

    public function handle()
    {
        $tahunAjaran = $this->argument('tahunAjaran') ?? $this->ask('Tahun ajaran');
        $semester = $this->argument('semester') ?? $this->ask('Semester');
        $periodeType = $this->argument('periodeType') ?? $this->ask('Periode type (weekly/monthly/semesterly)');
        $periodNumber = $this->argument('periodNumber') ?? $this->ask('Nomor minggu/bulan/semester (opsional, boleh kosong)');

        $students = Student::whereHas('classroom')->get();

        foreach ($students as $student) {
            SummaryMemorizationByPeriod::dispatch(
                $student,
                $tahunAjaran,
                $semester,
                $periodeType,
                $periodNumber
            );
        }

        $this->info('Memorization summary jobs dispatched for all students.');
    }
}

