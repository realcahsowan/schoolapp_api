<?php

namespace App\Filament\AdminTahfidz\Resources\KalenderHafalans\Pages;

use App\Filament\AdminTahfidz\Resources\KalenderHafalans\KalenderHafalanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKalenderHafalans extends ListRecords
{
    protected static string $resource = KalenderHafalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
