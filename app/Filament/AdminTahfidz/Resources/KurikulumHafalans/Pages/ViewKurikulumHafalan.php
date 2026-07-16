<?php

namespace App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Pages;

use App\Filament\AdminTahfidz\Resources\KurikulumHafalans\KurikulumHafalanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewKurikulumHafalan extends ViewRecord
{
    protected static string $resource = KurikulumHafalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
        ];
    }
}
