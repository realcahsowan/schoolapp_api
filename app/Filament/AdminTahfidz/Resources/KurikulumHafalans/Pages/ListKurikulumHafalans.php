<?php

namespace App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Pages;

use App\Filament\AdminTahfidz\Resources\KurikulumHafalans\KurikulumHafalanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKurikulumHafalans extends ListRecords
{
    protected static string $resource = KurikulumHafalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
