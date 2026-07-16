<?php

namespace App\Filament\TataUsaha\Resources\Guardians\Pages;

use App\Filament\TataUsaha\Resources\Guardians\GuardianResource;
use App\Filament\TataUsaha\Resources\Guardians\RelationManagers;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGuardian extends ViewRecord
{
    protected static string $resource = GuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
        ];
    }
}
