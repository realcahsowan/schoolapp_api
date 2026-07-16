<?php

namespace App\Filament\AdminTahfidz\Resources\KurikulumHafalans\Pages;

use App\Filament\AdminTahfidz\Resources\KurikulumHafalans\KurikulumHafalanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditKurikulumHafalan extends EditRecord
{
    protected static string $resource = KurikulumHafalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
