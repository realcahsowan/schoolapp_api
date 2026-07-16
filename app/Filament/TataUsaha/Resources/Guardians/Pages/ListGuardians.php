<?php

namespace App\Filament\TataUsaha\Resources\Guardians\Pages;

use App\Filament\TataUsaha\Resources\Guardians\GuardianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGuardians extends ListRecords
{
    protected static string $resource = GuardianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
