<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Settings\GeneralSettings;

class CurrentYearSemesterScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $settings = app(GeneralSettings::class);
        $builder->where('tahun_ajaran', $settings->tahun_ajaran)
                ->where('semester', $settings->semester);
    }
}
