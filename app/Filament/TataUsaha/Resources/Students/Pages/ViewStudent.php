<?php

namespace App\Filament\TataUsaha\Resources\Students\Pages;

use App\Filament\TataUsaha\Resources\Students\RelationManagers\ClassroomsRelationManager;
use App\Filament\TataUsaha\Resources\Students\RelationManagers\DormitoriesRelationManager;
use App\Filament\TataUsaha\Resources\Students\RelationManagers\GuardiansRelationManager;
use App\Filament\TataUsaha\Resources\Students\RelationManagers\MurobbisRelationManager;
use App\Filament\TataUsaha\Resources\Students\StudentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            GuardiansRelationManager::class,
            MurobbisRelationManager::class,
            ClassroomsRelationManager::class,
            // DormitoriesRelationManager::class,
        ];
    }
}
