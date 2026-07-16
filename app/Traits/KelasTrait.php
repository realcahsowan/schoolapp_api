<?php

namespace App\Traits;

trait KelasTrait
{
    public static function getClassroomsOptions($school)
    {
        $school->load(['classrooms' => fn ($q) => $q->currentYear() ]);
        return $school->classrooms->pluck('nama', 'id')->sort();
    }
}
