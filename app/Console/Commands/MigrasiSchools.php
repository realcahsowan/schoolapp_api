<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\School;

class MigrasiSchools extends Command
{
    protected $signature = 'migrasi:schools';
    protected $description = 'Migrasi data schools dari database lama';

    public function handle()
    {
        $schools = DB::connection('madrasah')->table('schools')->get();
        $schoolInserts = [];
        foreach ($schools as $oldSchool) {
            $schoolData = (array) $oldSchool;
            $schoolData['id'] = $oldSchool->id;
            $schoolInserts[] = $schoolData;
        }
        if ($schoolInserts) School::insert($schoolInserts);

        $this->info('Migrasi schools selesai!');
    }
}