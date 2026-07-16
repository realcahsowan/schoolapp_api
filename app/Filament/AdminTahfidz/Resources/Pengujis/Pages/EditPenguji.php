<?php

namespace App\Filament\AdminTahfidz\Resources\Pengujis\Pages;

use App\Filament\AdminTahfidz\Resources\Pengujis\PengujiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPenguji extends EditRecord
{
    protected static string $resource = PengujiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
