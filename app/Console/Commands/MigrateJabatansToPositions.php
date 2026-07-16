<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateJabatansToPositions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrasi:jabatans-to-positions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi data dari db madrasah tabel jabatans ke db utama tabel positions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Ambil data dari db madrasah
        $jabatans = DB::connection('madrasah')->table('jabatans')->get();

        $this->info("Migrasi {$jabatans->count()} data...");

        DB::transaction(function () use ($jabatans) {
            foreach ($jabatans as $jabatan) {
                DB::table('positions')->updateOrInsert(
                    ['id' => $jabatan->id],
                    [
                        'nama'        => $jabatan->nama,
                        'sk'          => $jabatan->sk,
                        'mulai'       => $jabatan->mulai,
                        'selesai'     => $jabatan->selesai,
                        'active'      => $jabatan->active,
                        'employee_id' => $jabatan->employee_id,
                        'school_id'   => $jabatan->school_id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]
                );
            }
        });

        $this->info('Migrasi selesai.');
    }
}
