<?php

namespace App\Filament\AdminTahfidz\Resources\Murobbis\Pages;

use App\Filament\AdminTahfidz\Resources\Murobbis\MurobbiResource;
use App\Filament\AdminTahfidz\Resources\Murobbis\RelationManagers;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMurobbi extends ViewRecord
{
    protected static string $resource = MurobbiResource::class;

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
            RelationManagers\JournalSummariesRelationManager::class,
        ];
    }
}
