<?php

namespace App\Filament\Resources\Dormitories\Pages;

use App\Filament\Resources\Dormitories\DormitoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDormitory extends EditRecord
{
    protected static string $resource = DormitoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
