<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Dormitory;

class MigrasiDormitories extends Command
{
    protected $signature = 'migrasi:dormitories';
    protected $description = 'Migrasi data dormitories dan pivot dormitory_employee, dormitory_student dari database lama';

    public function handle()
    {
        DB::transaction(function () {
            // Migrasi dormitories
            $dormitories = DB::connection('madrasah')->table('dormitories')->get();
            $dormitoryInserts = [];
            foreach ($dormitories as $oldDormitory) {
                $dormitoryData = (array) $oldDormitory;
                $dormitoryData['id'] = $oldDormitory->id; // preserve old id
                $dormitoryInserts[] = $dormitoryData;
            }
            if ($dormitoryInserts) Dormitory::insert($dormitoryInserts);

            // Migrasi pivot dormitory_employee
            $dormitoryEmployee = DB::connection('madrasah')->table('dormitory_employee')->get();
            $pivotDormitoryEmployee = [];
            $validEmployeeIds = DB::table('employees')->pluck('id')->toArray();
            foreach ($dormitoryEmployee as $pivot) {
                if (in_array($pivot->employee_id, $validEmployeeIds)) {
                    $pivotDormitoryEmployee[] = [
                        'dormitory_id' => $pivot->dormitory_id,
                        'employee_id' => $pivot->employee_id,
                        'room' => $pivot->room ?? null,
                        'is_active' => $pivot->active ?? false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if ($pivotDormitoryEmployee) DB::table('dormitory_employee')->insert($pivotDormitoryEmployee);

            // Migrasi pivot dormitory_student
            $dormitoryStudent = DB::connection('madrasah')->table('dormitory_student')->get();
            $pivotDormitoryStudent = [];
            $validStudentIds = DB::table('students')->pluck('id')->toArray();
            $validDormitoryIds = DB::table('dormitories')->pluck('id')->toArray();
            foreach ($dormitoryStudent as $pivot) {
                if (in_array($pivot->student_id, $validStudentIds) && in_array($pivot->dormitory_id, $validDormitoryIds)) {
                    $pivotDormitoryStudent[] = [
                        'dormitory_id' => $pivot->dormitory_id,
                        'student_id' => $pivot->student_id,
                        'room' => $pivot->room ?? null,
                        'is_active' => $pivot->active ?? false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if ($pivotDormitoryStudent) DB::table('dormitory_student')->insert($pivotDormitoryStudent);
        });

        $this->info('Migrasi dormitories dan pivot selesai!');
    }
}
