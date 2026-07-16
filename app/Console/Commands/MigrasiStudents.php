<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;

class MigrasiStudents extends Command
{
    protected $signature = 'migrasi:students';
    protected $description = 'Migrasi data students dan users terkait dari database lama';

    public function handle()
    {
        $oldUsers = DB::connection('madrasah')->table('users')->get();
        $usersById = collect($oldUsers)->keyBy('id');

        $students = DB::connection('madrasah')->table('students')->get();
        $studentInserts = [];
        $userStudentInserts = [];
        foreach ($students as $oldStudent) {
            $studentData = (array) $oldStudent;
            $studentData['id'] = $oldStudent->id;
            unset($studentData['user_id']);
            unset($studentData['school_id']);
            if (!isset($studentData['has_siblings']) || is_null($studentData['has_siblings'])) {
                $studentData['has_siblings'] = 0;
            }
            $studentData['is_beasiswa'] = 0;
            if (array_key_exists('active', $studentData)) {
                $studentData['is_active'] = $studentData['active'];
                unset($studentData['active']);
            }
            unset($studentData['graduated']);
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

        $this->info('Migrasi students dan users selesai!');
    }
}