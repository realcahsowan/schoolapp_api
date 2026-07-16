<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Employee;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\Education;
use App\Models\Institution;
use App\Models\School;

class MigrasiUsers extends Command
{
    protected $signature = 'migrasi:users';
    protected $description = 'Migrasi data users, employees, students, guardians, institutions, schools dari database lama';

    public function handle()
    {
        DB::transaction(function () {
            // Migrate Institutions first
            $institutions = DB::connection('madrasah')->table('institutions')->get();
            $institutionInserts = [];
            foreach ($institutions as $oldInstitution) {
                $institutionData = (array) $oldInstitution;
                $institutionData['id'] = $oldInstitution->id; // preserve old id
                $institutionInserts[] = $institutionData;
            }
            if ($institutionInserts) Institution::insert($institutionInserts);

            // Migrate Schools after Institutions
            $schools = DB::connection('madrasah')->table('schools')->get();
            $schoolInserts = [];
            foreach ($schools as $oldSchool) {
                $schoolData = (array) $oldSchool;
                $schoolData['id'] = $oldSchool->id; // preserve old id
                $schoolInserts[] = $schoolData;
            }
            if ($schoolInserts) School::insert($schoolInserts);

            // Valid columns for employees table
            $employeeColumns = [
                'id', 'nama', 'nik', 'nip', 'tempat_lahir', 'tanggal_lahir', 'gender', 'alamat', 'telepon',
                'file_foto', 'file_signature', 'institution_id', 'angkatan_stipi', 'nuptk', 'peg_id', 'golongan_darah',
                'tanggal_mulai_bekerja', 'status_perkawinan', 'nama_ayah', 'nama_ibu', 'nama_pasangan', 'jumlah_anak',
                'riwayat_mengajar', 'pendidikan_terakhir', 'created_at', 'updated_at'
            ];

            // Valid columns for users table
            $userColumns = [
                'id', 'name', 'email', 'password', 'employee_id', 'student_id', 'guardian_id', 'created_at', 'updated_at'
            ];

            // Fetch all users from old DB
            $oldUsers = DB::connection('madrasah')->table('users')->get();
            $usersById = collect($oldUsers)->keyBy('id');

            // Prepare bulk insert for Employees
            $employees = DB::connection('madrasah')->table('employees')->get();
            $employeeInserts = [];
            $userEmployeeInserts = [];
            $educationInserts = [];
            $validInstitutionIds = Institution::pluck('id')->toArray();
            foreach ($employees as $oldEmployee) {
                $employeeData = (array) $oldEmployee;
                $employeeData['id'] = $oldEmployee->id; // preserve old id
                unset($employeeData['user_id']);
                // Set institution_id to null if not found
                if (!empty($employeeData['institution_id']) && !in_array($employeeData['institution_id'], $validInstitutionIds)) {
                    $employeeData['institution_id'] = null;
                }
                // Rename nama_lembaga and lokasi_lembaga to nama_perguruan_tinggi and lokasi_perguruan_tinggi
                if (isset($employeeData['nama_lembaga'])) {
                    $employeeData['nama_perguruan_tinggi'] = $employeeData['nama_lembaga'];
                    unset($employeeData['nama_lembaga']);
                }
                if (isset($employeeData['lokasi_lembaga'])) {
                    $employeeData['lokasi_perguruan_tinggi'] = $employeeData['lokasi_lembaga'];
                    unset($employeeData['lokasi_lembaga']);
                }
                // If employee has fakultas, create education record
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
                // Unset nama_perguruan_tinggi and lokasi_perguruan_tinggi
                unset($employeeData['nama_perguruan_tinggi'], $employeeData['lokasi_perguruan_tinggi']);
                // Filter only valid columns
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

            // Prepare bulk insert for Students
            $students = DB::connection('madrasah')->table('students')->get();
            $studentInserts = [];
            $userStudentInserts = [];
            foreach ($students as $oldStudent) {
                $studentData = (array) $oldStudent;
                $studentData['id'] = $oldStudent->id; // preserve old id
                unset($studentData['user_id']);
                unset($studentData['school_id']);
                // Set has_siblings to 0 if null
                if (!isset($studentData['has_siblings']) || is_null($studentData['has_siblings'])) {
                    $studentData['has_siblings'] = 0;
                }
                // Set is_beasiswa to 0
                $studentData['is_beasiswa'] = 0;
                // Rename active to is_active
                if (array_key_exists('active', $studentData)) {
                    $studentData['is_active'] = $studentData['active'];
                    unset($studentData['active']);
                }
                // Rename graduated to is_graduated
                if (array_key_exists('graduated', $studentData)) {
                    unset($studentData['graduated']);
                }
                $studentData['is_graduated'] = 0;
                if (isset($studentData['riwayat_kelas'])) {
                    $studentData['riwayat_kelas'] = json_encode($studentData['riwayat_kelas']);
                }
                $studentInserts[] = $studentData;
                $oldUser = !empty($oldStudent->user_id) ? $usersById->get($oldStudent->user_id) : null;
                if ($oldUser) {
                    $userData = (array) $oldUser;
                    $userData['student_id'] = $oldStudent->id;
                    unset($userData['id'], $userData['impersonation_code'], $userData['impersonation_expired_at']);
                    if (isset($userData['roles']) && is_array($userData['roles']) && count($userData['roles']) > 0) {
                        $userData['role'] = $userData['roles'][0];
                    }
                    unset($userData['roles']);
                    $userStudentInserts[] = $userData;
                }
            }
            if ($studentInserts) Student::insert($studentInserts);
            if ($userStudentInserts) User::insert($userStudentInserts);

            // Prepare bulk insert for Guardians (ortus)
            $guardians = DB::connection('madrasah')->table('ortus')->get();
            $guardianInserts = [];
            $userGuardianInserts = [];
            $guardianSchoolPivot = [];
            foreach ($guardians as $oldGuardian) {
                $guardianData = (array) $oldGuardian;
                $guardianData['id'] = $oldGuardian->id; // preserve old id
                unset($guardianData['user_id']);
                unset($guardianData['school_id']);
                $guardianData['modifed_by_owner'] = 0;
                $guardianInserts[] = $guardianData;
                $oldUser = !empty($oldGuardian->user_id) ? $usersById->get($oldGuardian->user_id) : null;
                if ($oldUser) {
                    $userData = (array) $oldUser;
                    $userData['guardian_id'] = $oldGuardian->id;
                    unset($userData['id'], $userData['impersonation_code'], $userData['impersonation_expired_at']);
                    if (isset($userData['roles']) && is_array($userData['roles']) && count($userData['roles']) > 0) {
                        $userData['role'] = $userData['roles'][0];
                    }
                    unset($userData['roles']);
                    $userGuardianInserts[] = $userData;
                }
                // Pivot guardian_school
                if (!empty($oldGuardian->school_id)) {
                    $guardianSchoolPivot[] = [
                        'guardian_id' => $oldGuardian->id,
                        'school_id' => $oldGuardian->school_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if ($guardianInserts) Guardian::insert($guardianInserts);
            if ($userGuardianInserts) User::insert($userGuardianInserts);
            if ($guardianSchoolPivot) DB::table('guardian_school')->insert($guardianSchoolPivot);
        });

        $this->info('Migrasi selesai!');
    }
}
