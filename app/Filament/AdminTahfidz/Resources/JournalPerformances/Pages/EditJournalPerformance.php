<?php

namespace App\Filament\AdminTahfidz\Resources\JournalPerformances\Pages;

use App\Filament\AdminTahfidz\Resources\JournalPerformances\JournalPerformanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJournalPerformance extends EditRecord
{
    protected static string $resource = JournalPerformanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
