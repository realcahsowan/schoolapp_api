<?php

namespace App\Filament\AdminTahfidz\Resources\KalenderHafalans\Pages;

use App\Filament\AdminTahfidz\Resources\KalenderHafalans\KalenderHafalanResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewKalenderHafalan extends ViewRecord
{
    protected static string $resource = KalenderHafalanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Detail Kalender ' . $this->record->tanggal->format('d F Y');
    }
}
