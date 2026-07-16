<?php

namespace App\Filament\AdminTahfidz\Resources\Examinations\Pages;

use App\Filament\AdminTahfidz\Resources\Examinations\ExaminationResource;
use App\Filament\AdminTahfidz\Resources\Examinations\RelationManagers\MistakesRelationManager;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\AdminTahfidz\Resources\Students\Pages\ViewStudent;

class ViewExamination extends ViewRecord
{
    protected static string $resource = ExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('View Student')
                ->url(fn ($record) => ViewStudent::getUrl(['record' => $this->record->student_id]) . '?relation=2')
                ->color('primary'),
            \Filament\Actions\Action::make('kunci_nilai')
                ->label('Kunci Nilai')
                ->color('success')
                ->icon('heroicon-o-lock-closed')
                ->disabled(fn () => $this->record->is_locked)
                ->action(function () {
                    if (!$this->record->is_locked) {
                        $this->record->is_locked = true;
                        $this->record->save();
                        \Filament\Notifications\Notification::make()
                            ->title('Nilai telah dikunci')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }

    public function getRelationManagers(): array
    {
        return [
            MistakesRelationManager::class,
        ];
    }
}
