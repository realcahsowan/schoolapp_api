<?php

namespace App\Traits;

use App\Models\Tahfidz\Configuration;
use App\Models\Murobbi;
use App\Models\Tahfidz\KalenderHafalan;
use Carbon\Carbon;

trait ReminderTrait
{
    public function getReminderMutabaahRecipients($schoolId, $waktu, $tanggal = null)
    {
        if (!in_array($waktu, ['pagi', 'sore'])) {
            return [];
        }

        if (is_null($tanggal)) {
            $tanggal = now();
        } else {
            $tanggal = Carbon::createFromFormat('Y-m-d', $tanggal);
        }

        if ($tanggal->isWeekend()) {
            return [];
        }

        $todayCalendar = KalenderHafalan::whereDate('tanggal', $tanggal->format('Y-m-d'))
            ->where('school_id', $schoolId)
            ->first();

        if (is_null($todayCalendar)) {
            return [];
        }

        $liburs = Configuration::where('name', 'liburMutabaahSore')
            ->where('school_id', $schoolId)
            ->first()?->payload;

        $libur = collect($liburs)->where('day', $tanggal->format('l'))->first();

        $emptyMutabaahMurobbis = Murobbi::where('school_id', $schoolId)->whereDoesntHave(
            'journalSummaries',
            fn($subQuery) => $subQuery->whereDate('tanggal', $tanggal->format('Y-m-d'))
        )
            ->with(['employee', 'students' => fn($sq) => $sq->wherePivot('is_active', true)])
            ->get();

        if ($waktu === 'sore' && !is_null($libur)) {
            $emptyMutabaahMurobbis = collect([]);
        }

        $partialMutabaahMurobbis = Murobbi::whereHas(
            'journalSummaries',
            fn($subQuery) => $subQuery->whereDate('tanggal', $tanggal->format('Y-m-d'))
                ->where('target_' . $waktu, '>', 0)
                ->where('completed_' . $waktu, false)
        )
            ->with([
                'employee',
                'students' => fn($sq) => $sq->wherePivot('is_active', true),
                'journalSummaries' => fn($subQuery) => $subQuery->whereDate('tanggal', $tanggal->format('Y-m-d')),
            ])
            ->get();

        $recipients = [];

        foreach ($emptyMutabaahMurobbis as $murobbi) {
            array_push($recipients, [
                'nama' => $murobbi->nama,
                'telepon' => $murobbi->employee->telepon,
                'employee_id' => $murobbi->employee->id,
                'school_id' => $murobbi->school_id,
                'incompleted_members' => $murobbi->students->pluck('nama'),
            ]);
        }

        foreach ($partialMutabaahMurobbis as $murobbi) {
            $progres = $murobbi->journalSummaries->first();
            $completedMembers = collect($progres?->input_summary)->where('waktu', $waktu)->keys();
            $incompletedMembers = $murobbi->students->whereNotIn('student_id', $completedMembers)->pluck('nama');
            array_push($recipients, [
                'nama' => $murobbi->nama,
                'telepon' => $murobbi->employee->telepon,
                'employee_id' => $murobbi->employee->id,
                'school_id' => $murobbi->school_id,
                'incompleted_members' => $incompletedMembers,
            ]);
        }

        return $recipients;
    }

    public function composeReminder($telepon, $message)
    {
        return [
            'recipient_type' => 'individual',
            'to' => $telepon,
            'type' => 'text',
            'text' => ['body' => $message],
        ];
    }
}
