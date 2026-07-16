<?php

namespace App\Models\Tahfidz;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Jobs\Tahfidz\GenerateExaminations;
use App\Settings\GeneralSettings;
use App\Models\Tahfidz\Examination;

class PengujiStudent extends Pivot
{
    protected $table = 'tahfidz__penguji_student';


    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (PengujiStudent $model) {
            $settings = app(GeneralSettings::class);
            if (empty($model->tahun_ajaran)) {
                $model->tahun_ajaran = $settings->tahun_ajaran;
            }
            if (empty($model->semester)) {
                $model->semester = $settings->semester;
            }
        });

        static::created(function (PengujiStudent $model) {
            GenerateExaminations::dispatch($model->student_id, $model->penguji_id);
            // Update total_students pada Penguji
            $penguji = \App\Models\Tahfidz\Penguji::find($model->penguji_id);
            if ($penguji) {
                $total = $penguji->students()->count();
                $penguji->update(['total_students' => $total]);
            }
        });

        static::deleting(function (PengujiStudent $model) {
            Examination::where('student_id', $model->student_id)
                ->where('penguji_id', $model->penguji_id)
                ->delete();
        });
    }
}
