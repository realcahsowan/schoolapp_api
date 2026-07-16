<?php

namespace App\Filament\AdminTahfidz\Resources\Examinations\Pages;

use App\Filament\AdminTahfidz\Resources\Examinations\ExaminationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditExamination extends EditRecord
{
    protected static string $resource = ExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
