<?php

namespace App\Filament\AdminTahfidz\Resources\Pengujis\Pages;

use App\Filament\AdminTahfidz\Resources\Pengujis\PengujiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListPengujis extends ListRecords
{
    protected static string $resource = PengujiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalWidth(Width::ExtraLarge),
        ];
    }
}
