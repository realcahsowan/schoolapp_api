<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Guardian;

class MigrasiGuardians extends Command
{
    protected $signature = 'migrasi:guardians';
    protected $description = 'Migrasi data guardians dan users terkait dari database lama';

    public function handle()
    {
        $oldUsers = DB::connection('madrasah')->table('users')->get();
        $usersById = collect($oldUsers)->keyBy('id');

        $guardians = DB::connection('madrasah')->table('ortus')->get();
        $guardianInserts = [];
        $userGuardianInserts = [];
        $guardianSchoolPivot = [];
        foreach ($guardians as $oldGuardian) {
            $guardianData = (array) $oldGuardian;
            $guardianData['id'] = $oldGuardian->id;
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

        $this->info('Migrasi guardians dan users selesai!');
    }
}