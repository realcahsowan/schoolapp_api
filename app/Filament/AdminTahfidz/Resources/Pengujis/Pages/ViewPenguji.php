<?php

namespace App\Filament\AdminTahfidz\Resources\Pengujis\Pages;

use App\Filament\AdminTahfidz\Resources\Pengujis\RelationManagers;
use App\Filament\AdminTahfidz\Resources\Pengujis\PengujiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\RelationManagers\RelationManager;

class ViewPenguji extends ViewRecord
{
    protected static string $resource = PengujiResource::class;

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
