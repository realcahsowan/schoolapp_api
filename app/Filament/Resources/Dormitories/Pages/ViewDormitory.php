<?php

namespace App\Filament\Resources\Dormitories\Pages;

use App\Filament\Resources\Dormitories\DormitoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDormitory extends ViewRecord
{
    protected static string $resource = DormitoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
