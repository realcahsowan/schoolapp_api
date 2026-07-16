<?php

namespace App\Filament\Resources\Guardians\Pages;

use App\Filament\Resources\Guardians\GuardianResource;
use App\Filament\Resources\Guardians\RelationManagers;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGuardian extends ViewRecord
{
    protected static string $resource = GuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            'students' => RelationManagers\StudentsRelationManager::class,
        ];
    }
}
