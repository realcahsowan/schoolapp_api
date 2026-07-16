<?php

namespace App\Filament\AdminTahfidz\Resources\Murobbis\Pages;

use App\Filament\AdminTahfidz\Resources\Murobbis\MurobbiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMurobbi extends EditRecord
{
    protected static string $resource = MurobbiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
