<?php

namespace App\Models;

use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'sk',
        'mulai',
        'selesai',
        'active',
        'employee_id',
        'school_id',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Position $position) {
            $position->syncSchoolUserAndMurobbi();
        });
        static::saved(function (Position $position) {
            $position->syncSchoolUserAndMurobbi();
        });
    }

    /**
     * Sync school_user relation and Murobbi logic.
     */
    private function syncSchoolUserAndMurobbi(): void
    {
        $this->load('employee.user');
        $oldRelationFound = DB::connection('mysql')->table('school_user')
            ->where('school_id', $this->school_id)
            ->where('user_id', $this->employee->user->id)
            ->exists();

        if ($this->active && ! $oldRelationFound) {
            DB::connection('mysql')->table('school_user')->insert([
                'school_id' => $this->school_id,
                'user_id' => $this->employee->user->id,
            ]);
        }

        if ($this->active && in_array($this->nama, ['murobbi', 'Murobbi'])) {
            Murobbi::firstOrCreate([
                'school_id' => $this->school_id,
                'employee_id' => $this->employee_id,
                'nama' => $this->employee->user->name,
                'gender' => $this->employee->gender,
                'tahun_ajaran' => app(GeneralSettings::class)->tahun_ajaran,
                'semester' => app(GeneralSettings::class)->semester,
            ]);
        }
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
