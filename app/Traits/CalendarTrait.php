<?php

namespace App\Traits;

trait CalendarTrait
{
    public static function getIndonesianDays()
    {
        return [
            'sun' => 'Ahad',
            'mon' => 'Senin',
            'tue' => 'Selasa',
            'wed' => 'Rabu',
            'thu' => 'Kamit',
            'fri' => 'Jum\'at',
            'sat' => 'Sabtu',
        ];
    }


    public static function getIndonesianMonths()
    {
        return [
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
    }
}
