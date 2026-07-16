<?php

namespace App\Jobs\Tahfidz;

use App\Models\Classroom;
use App\Models\PromotionBatch;
use App\Models\School;
use App\Settings\GeneralSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PromoteClassroomsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public array $classroomIds,
        public int $batchId,
        public int $schoolId,
    ) {}

    public function handle(): void
    {
        $currentYear = app(GeneralSettings::class)->tahun_ajaran;
        $schoolAliases = School::all()->pluck('alias', 'id');
        $batch = PromotionBatch::find($this->batchId);

        if (! $batch || $batch->completed) {
            return;
        }

        $classrooms = Classroom::whereIn('id', $this->classroomIds)
            ->where('is_promoted', false)
            ->get();

        foreach ($classrooms as $classroom) {
            $this->promoteClassroom($classroom, $currentYear, $schoolAliases, $batch);
        }

        $prevClassroomIds = Classroom::where('school_id', $this->schoolId)
            ->where('tahun_ajaran', $batch->tahun_ajaran_asal)
            ->pluck('id')
            ->toArray();

        $batch->refresh();
        $updatedClassrooms = $batch->classrooms ?? [];

        $batch->update([
            'completed' => empty(array_diff($prevClassroomIds, $updatedClassrooms)),
        ]);
    }

    private function promoteClassroom(
        Classroom $classroom,
        string $currentYear,
        iterable $schoolAliases,
        PromotionBatch $batch,
    ): void {
        $classroom->load('students');
        $isGraduation = in_array((int) $classroom->level, [6, 9, 12]);

        DB::transaction(function () use ($classroom, $currentYear, $isGraduation, $schoolAliases) {
            $studentIds = $classroom->students->pluck('id');

            DB::table('classroom_student')
                ->whereIn('student_id', $studentIds)
                ->update(['is_active' => false]);

            if ($isGraduation) {
                DB::table('students')
                    ->whereIn('id', $studentIds)
                    ->update(['is_graduated' => true]);

                $classroom->update(['is_promoted' => true]);

                return;
            }

            $newLevel = $classroom->level === 'idad' ? 10 : (int) $classroom->level + 1;
            $newNama = $newLevel . $classroom->rombel;

            $newClassroom = Classroom::create([
                'nama' => $newNama,
                'level' => (string) $newLevel,
                'rombel' => $classroom->rombel,
                'alias' => $newNama . '-' . Arr::get($schoolAliases, $classroom->school_id),
                'employee_id' => $classroom->employee_id,
                'school_id' => $classroom->school_id,
                'tingkat_id' => $newLevel + 2,
                'jurusan_id' => $classroom->jurusan_id,
                'kurikulum_id' => $classroom->kurikulum_id,
                'tahun_ajaran' => $currentYear,
            ]);

            $pivotData = [];
            $riwayatUpdates = [];

            foreach ($classroom->students as $student) {
                $pivotData[] = [
                    'student_id' => $student->id,
                    'classroom_id' => $newClassroom->id,
                    'is_active' => true,
                ];

                $riwayat = is_array($student->riwayat_kelas) ? $student->riwayat_kelas : [];
                $riwayat[] = [
                    'classroom_id' => $newClassroom->id,
                    'tahun_ajaran' => $currentYear,
                ];
                $riwayat[] = [
                    'classroom_id' => $classroom->id,
                    'tahun_ajaran' => $classroom->tahun_ajaran,
                ];

                $riwayatUpdates[] = [
                    'id' => $student->id,
                    'riwayat_kelas' => json_encode($riwayat),
                ];
            }

            DB::table('students')->whereIn('id', $studentIds)->update(['classroom_id' => $newClassroom->id]);

            foreach ($riwayatUpdates as $update) {
                DB::table('students')->where('id', $update['id'])->update(['riwayat_kelas' => $update['riwayat_kelas']]);
            }

            DB::table('classroom_student')->insert($pivotData);

            $classroom->update(['is_promoted' => true]);
        });

        $batch->refresh();
        $updatedClassrooms = $batch->classrooms;
        $updatedClassrooms[] = $classroom->id;
        $batch->update(['classrooms' => array_unique($updatedClassrooms)]);
    }
}
