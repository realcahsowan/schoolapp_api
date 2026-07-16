<?php

namespace App\Filament\TataUsaha\Resources\Classrooms\Pages;

use App\Filament\TataUsaha\Resources\Classrooms\ClassroomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClassrooms extends ListRecords
{
    protected static string $resource = ClassroomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
