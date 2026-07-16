<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class StudentMurobbi extends Pivot
{
    protected $table = 'tahfidz__student_murobbi';

    protected $fillable = [
        'student_id', 'murobbi_id', 'category', 'program', 'is_active',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->is_active) {
                static::where('student_id', $model->student_id)
                    ->where('is_active', true)
                    ->where('id', '!=', $model->id ?? 0)
                    ->update(['is_active' => false]);
            }
        });

        static::created(function ($model) {
            // Only fire when is_active is true
            // if (! $model->is_active) {
            //     return;
            // }

            $student = \App\Models\Student::with('classroom')->find($model->student_id);
            if (! $student || ! $student->classroom) {
                return;
            }

            $tahunAjaran = app(\App\Settings\GeneralSettings::class)->tahun_ajaran;
            $semester = app(\App\Settings\GeneralSettings::class)->semester;
            $schoolId = $student->classroom->school_id;
            $program = $model->program;
            $category = $model->category;

            // Get the PAS Juz Map
            $config = \App\Models\Tahfidz\Configuration::where('school_id', $schoolId)
                ->where('name', 'juzUjian')->first();
            $payload = $config?->payload ?? [];
            $detail = collect($payload)->where('semester', $semester)->first();
            $found = collect(\Illuminate\Support\Arr::get($detail, 'detail', []))
                ->where('program', $program)
                ->first();

            $pasJuzMap = collect(\Illuminate\Support\Arr::get($found, 'juz_map', []))
                ->where('grade', $student->classroom->level)
                ->first();
            $juzArray = \Illuminate\Support\Arr::get($pasJuzMap, 'juz', []);
            $juzArray = array_map('intval', $juzArray);

            // Create or Update the Rapor
            // Create or update Rapor sesuai constraint unik di DB
            \App\Models\Tahfidz\Rapor::updateOrCreate([
                'student_id' => $model->student_id,
                'tahun_ajaran' => $tahunAjaran,
                'semester' => $semester,
            ], [
                'murobbi_employee_id' => $model->murobbi_id,
                'category' => $category,
                'program' => $program,
                'pas_juz_map' => $juzArray,
                'total_juz_pas' => is_array($juzArray) ? count($juzArray) : 0,
            ]);
        });
    }
}
