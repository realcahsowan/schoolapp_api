<?php

namespace App\Filament\AdminTahfidz\Resources\Examinations\Pages;

use App\Filament\AdminTahfidz\Resources\Examinations\ExaminationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExaminations extends ListRecords
{
    protected static string $resource = ExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
