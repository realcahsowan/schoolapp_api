<?php

namespace App\Filament\AdminTahfidz\Resources\KalenderHafalans\Pages;

use App\Filament\AdminTahfidz\Resources\KalenderHafalans\KalenderHafalanResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditKalenderHafalan extends EditRecord
{
    protected static string $resource = KalenderHafalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Edit Kalender ' . $this->record->tanggal->format('d F Y');
    }
}
