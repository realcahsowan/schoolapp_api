<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Filament\Resources\Employees\RelationManagers;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            RelationManagers\PositionsRelationManager::class,
            RelationManagers\DormitoriesRelationManager::class,
            RelationManagers\MurobbisRelationManager::class,
        ];
    }
}
