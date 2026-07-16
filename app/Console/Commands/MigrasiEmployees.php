<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Employee;
use App\Models\Education;
use App\Models\Institution;

class MigrasiEmployees extends Command
{
    protected $signature = 'migrasi:employees';
    protected $description = 'Migrasi data employees dan users terkait dari database lama';

    public function handle()
    {
        $employeeColumns = [
            'id', 'nama', 'nik', 'nip', 'tempat_lahir', 'tanggal_lahir', 'gender', 'alamat', 'telepon',
            'file_foto', 'file_signature', 'institution_id', 'angkatan_stipi', 'nuptk', 'peg_id', 'golongan_darah',
            'tanggal_mulai_bekerja', 'status_perkawinan', 'nama_ayah', 'nama_ibu', 'nama_pasangan', 'jumlah_anak',
            'riwayat_mengajar', 'pendidikan_terakhir', 'created_at', 'updated_at'
        ];

        // $userColumns = [
        //     'id', 'name', 'email', 'password', 'employee_id', 'created_at', 'updated_at'
        // ];

        $oldUsers = DB::connection('madrasah')->table('users')->get();
        $usersById = collect($oldUsers)->keyBy('id');

        $employees = DB::connection('madrasah')->table('employees')->get();
        $employeeInserts = [];
        $userEmployeeInserts = [];
        $educationInserts = [];
        $validInstitutionIds = Institution::pluck('id')->toArray();

        foreach ($employees as $oldEmployee) {
            $employeeData = (array) $oldEmployee;
            $employeeData['id'] = $oldEmployee->id;
            unset($employeeData['user_id']);
            if (!empty($employeeData['institution_id']) && !in_array($employeeData['institution_id'], $validInstitutionIds)) {
                $employeeData['institution_id'] = null;
            }
            if (isset($employeeData['nama_lembaga'])) {
                $employeeData['nama_perguruan_tinggi'] = $employeeData['nama_lembaga'];
                unset($employeeData['nama_lembaga']);
            }
            if (isset($employeeData['lokasi_lembaga'])) {
                $employeeData['lokasi_perguruan_tinggi'] = $employeeData['lokasi_lembaga'];
                unset($employeeData['lokasi_lembaga']);
            }
            if (!empty($employeeData['fakultas'])) {
                $educationInserts[] = [
                    'employee_id' => $oldEmployee->id,
                    'nama_lembaga' => $employeeData['nama_perguruan_tinggi'] ?? null,
                    'lokasi_lembaga' => $employeeData['lokasi_perguruan_tinggi'] ?? null,
                    'fakultas' => $employeeData['fakultas'],
                    'jurusan' => $employeeData['jurusan'] ?? null,
                    'tahun_masuk' => $employeeData['tahun_masuk'] ?? null,
                    'tahun_lulus' => $employeeData['tahun_lulus'] ?? null,
                    'nomor_induk' => $employeeData['nomor_induk_mahasiswa'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                unset($employeeData['fakultas'], $employeeData['jurusan'], $employeeData['tahun_masuk'], $employeeData['tahun_lulus'], $employeeData['nomor_induk_mahasiswa']);
            }
            unset($employeeData['nama_perguruan_tinggi'], $employeeData['lokasi_perguruan_tinggi']);
            $employeeData = array_intersect_key($employeeData, array_flip($employeeColumns));
            $employeeInserts[] = $employeeData;
            $oldUser = !empty($oldEmployee->user_id) ? $usersById->get($oldEmployee->user_id) : null;
            if ($oldUser) {
                $userData = (array) $oldUser;
                $userData['employee_id'] = $oldEmployee->id;
                unset($userData['id'], $userData['impersonation_code'], $userData['impersonation_expired_at']);
                if (isset($userData['roles']) && is_array($userData['roles']) && count($userData['roles']) > 0) {
                    $userData['role'] = $userData['roles'][0];
                }
                unset($userData['roles']);
                $userEmployeeInserts[] = $userData;
            }
        }
        if ($employeeInserts) Employee::insert($employeeInserts);
        if ($userEmployeeInserts) User::insert($userEmployeeInserts);
        if ($educationInserts) Education::insert($educationInserts);

        $this->info('Migrasi employees dan users selesai!');
    }
}