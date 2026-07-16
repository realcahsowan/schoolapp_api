<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Institution;

class MigrasiInstitutions extends Command
{
    protected $signature = 'migrasi:institutions';
    protected $description = 'Migrasi data institutions dari database lama';

    public function handle()
    {
        $institutions = DB::connection('madrasah')->table('institutions')->get();
        $institutionInserts = [];
        foreach ($institutions as $oldInstitution) {
            $institutionData = (array) $oldInstitution;
            $institutionData['id'] = $oldInstitution->id;
            $institutionInserts[] = $institutionData;
        }
        if ($institutionInserts) Institution::insert($institutionInserts);

        $this->info('Migrasi institutions selesai!');
    }
}